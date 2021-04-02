<template>
	<section>
		<div class="row">
			<div class="col-md-6">
				<div class="card shadow mb-4">
					<div class="card-body">
						<div class="form-group">
							<label for>Product Name</label>
							<input class="form-control" placeholder="Product Name" type="text" v-model="product_name" />
						</div>
						<div class="form-group">
							<label for>Product SKU</label>
							<input class="form-control" placeholder="Product Name" type="text" v-model="product_sku" />
						</div>
						<div class="form-group">
							<label for>Description</label>
							<textarea class="form-control" cols="30" id rows="4" v-model="description"></textarea>
						</div>
					</div>
				</div>

				<div class="card shadow mb-4">
					<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
						<h6 class="m-0 font-weight-bold text-primary">Media</h6>
					</div>
					<div class="card-body border">
						<vue-dropzone :options="dropzoneOptions" id="dropzone" ref="myVueDropzone"></vue-dropzone>
					</div>
				</div>
			</div>

			<div class="col-md-6">
				<div class="card shadow mb-4">
					<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
						<h6 class="m-0 font-weight-bold text-primary">Variants</h6>
					</div>
					<div class="card-body">
						<div class="row" v-for="(item,index) in product_variant">
							<div class="col-md-4">
								<div class="form-group">
									<label for>Option</label>
									<select class="form-control" v-model="item.option">
										<option :value="variant.id" v-for="variant in variants">{{ variant.title }}</option>
									</select>
								</div>
							</div>
							<div class="col-md-8">
								<div class="form-group">
									<label @click="product_variant.splice(index,1); checkVariant" class="float-right text-primary" style="cursor: pointer;" v-if="product_variant.length != 1">Remove</label>
									<label for v-else>.</label>
									<input-tag @input="checkVariant" class="form-control" v-model="item.tags"></input-tag>
								</div>
							</div>
						</div>
					</div>
					<div class="card-footer" v-if="product_variant.length < variants.length && product_variant.length < 3">
						<button @click="newVariant" class="btn btn-primary">Add another option</button>
					</div>

					<div class="card-header text-uppercase">Preview</div>
					<div class="card-body">
						<div class="table-responsive">
							<table class="table">
								<thead>
									<tr>
										<td>Variant</td>
										<td>Price</td>
										<td>Stock</td>
									</tr>
								</thead>
								<tbody>
									<tr v-for="variant_price in product_variant_prices">
										<td>{{ variant_price.title }}</td>
										<td>
											<input class="form-control" type="text" v-model="variant_price.price" />
										</td>
										<td>
											<input class="form-control" type="text" v-model="variant_price.stock" />
										</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>

		<button @click="submitProduct('update')" class="btn btn-lg btn-primary" type="submit" v-if="product">Update</button>
		<button @click="submitProduct('save')" class="btn btn-lg btn-primary" type="submit" v-else>Save</button>
		<button class="btn btn-secondary btn-lg" type="button">Cancel</button>
	</section>
</template>

<script>
import vue2Dropzone from "vue2-dropzone";
import "vue2-dropzone/dist/vue2Dropzone.min.css";
import InputTag from "vue-input-tag";

export default {
	components: {
		vueDropzone: vue2Dropzone,
		InputTag,
	},
	props: ["variants", "product"],
	data() {
		return {
			product_name: "",
			product_sku: "",
			description: "",
			images: [],
			product_variant: [
				{
					option: this.variants[0].id,
					tags: [],
				},
			],
			product_variant_prices: [],
			dropzoneOptions: {
				url: "https://httpbin.org/post",
				thumbnailWidth: 150,
				maxFilesize: 0.5,
				headers: { "My-Awesome-Header": "header value" },
			},
		};
	},
	methods: {
		// it will push a new object into product variant
		newVariant() {
			let all_variants = this.variants.map((el) => el.id);
			let selected_variants = this.product_variant.map((el) => el.option);
			let available_variants = all_variants.filter(
				(entry1) => !selected_variants.some((entry2) => entry1 == entry2)
			);
			// console.log(available_variants)

			this.product_variant.push({
				option: available_variants[0],
				tags: [],
			});
		},

		// check the variant and render all the combination
		checkVariant() {
			let tags = [];
			this.product_variant_prices = [];
			this.product_variant.filter((item) => {
				tags.push(item.tags);
			});

			this.getCombn(tags).forEach((item) => {
				this.product_variant_prices.push({
					title: item,
					price: 0,
					stock: 0,
				});
			});
		},

		// combination algorithm
		getCombn(arr, pre) {
			pre = pre || "";
			if (!arr.length) {
				return pre;
			}
			let self = this;
			let ans = arr[0].reduce(function (ans, value) {
				return ans.concat(self.getCombn(arr.slice(1), pre + value + "/"));
			}, []);
			return ans;
		},

		// store product into database
		submitProduct(type) {
			let product = {
				title: this.product_name,
				sku: this.product_sku,
				description: this.description,
				product_image: this.images,
				product_variant: this.product_variant,
				product_variant_prices: this.product_variant_prices,
			};
			if (type == "update") {
				axios
					.put("/product/" + this.product.id, product)
					.then((response) => {
						console.log(response.data);
					})
					.catch((error) => {
						console.log(error);
					});
			}
			if (type == "save") {
				axios
					.post("/product", product)
					.then((response) => {
						console.log(response.data);
					})
					.catch((error) => {
						console.log(error);
					});
			}
		},
		setEditValue: function () {
			const self = this;
			self.product_name = self.product.title;
			self.product_sku = self.product.sku;
			self.description = self.product.description;
			let variants = self.product.variants.map((x) => {
				return x.variant_id;
			});
			variants = variants.filter((value, index, _this) => {
				return _this.indexOf(value) === index;
			});
			self.product_variant = [];
			variants.map((x) => {
				self.product_variant.push({ option: x, tags: [] });
			});
			self.product_variant.map((x) => {
				self.product.variants.map((el) => {
					if (x.option == el.variant_id) {
						x.tags.push(el.variant);
					}
				});
			});
			self.checkVariant();
			this.product_variant_prices.forEach(function (value, index) {
				value.price = self.product.variant_prices[index].price;
				value.stock = self.product.variant_prices[index].stock;
			});
		},
	},
	mounted() {
		if (this.product) {
			this.setEditValue();
		}
		console.log("Component mounted.");
	},
};
</script>
