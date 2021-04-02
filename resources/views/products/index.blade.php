@extends('layouts.app')

@section('content')

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Products</h1>
    </div>


    <div class="card">
        <form action="/product" method="get" class="card-header">
            @csrf
            <div class="form-row justify-content-between">
                <div class="col-md-2">
                    <input type="text" name="title" placeholder="Product Title" class="form-control" value="{{ app('request')->input('title') }}">
                </div>
                <div class="col-md-2">
                    <select name="variant" id="" class="form-control">
                        <option selected hidden disabled>--CHOOSE VARIANT--</option>
                        @foreach ($variants as $variant)
                            <optgroup label="{{ $variant['title'] }}">
                            @foreach ($variant['product_variations'] as $value)
                                <option value="{{ $value['id'] }}" {{ app('request')->input('variant')==$value['id'] ? 'selected' :' ' }}>{{ $value['variant'] }}</option>
                            @endforeach
                        </optgroup>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">Price Range</span>
                        </div>
                        <input type="text" name="price_from" aria-label="First name" placeholder="From" class="form-control" value="{{ app('request')->input('price_from') }}">
                        <input type="text" name="price_to" aria-label="Last name" placeholder="To" class="form-control" value="{{ app('request')->input('price_to') }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <input type="date" name="date" placeholder="Date" class="form-control" value="{{ app('request')->input('date') }}">
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary float-right"><i class="fa fa-search"></i></button>
                </div>
            </div>
        </form>

        <div class="card-body">
            <div class="table-response">
                <table class="table">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Title</th>
                        <th style="width:40%">Description</th>
                        <th>Variant</th>
                        <th width="150px">Action</th>
                    </tr>
                    </thead>
                    @php($i=1+(($products->currentPage()-1)*2))
                    <tbody>
                        @foreach ($products as $key=>$value)
                            <tr>
                                <td>{{ $i++ }}</td>
                                <td>{{ $value->title }} <br> Created at : {{ \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $value->created_at)->format("d-M-Y") }}</td>
                                <td>{{ $value->description }}</td>
                                <td>
                                    <dl class="row mb-0" style="height: 80px; overflow: hidden" id="variant{{ $i }}">
                                    @foreach ($value->variant_prices as $in_key=>$variant)
                                        <dt class="col-sm-3 pb-0">
                                            {{ $variant->variant_one->variant }}/ {{ $variant->variant_two->variant }}/ {{ $variant->variant_three->variant }}
                                        </dt>
                                        <dd class="col-sm-9">
                                            <dl class="row mb-0">
                                                <dt class="col-sm-4 pb-0">Price : {{ number_format($variant->price,2) }}</dt>
                                                <dd class="col-sm-8 pb-0">InStock : {{ number_format($variant->stock,2) }}</dd>
                                            </dl>
                                        </dd>
                                        @endforeach
                                    </dl>
                                    <button onclick="$('#variant{{ $i }}').toggleClass('h-auto')" class="btn btn-sm btn-link">Show more</button>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('product.edit', $value->id) }}" class="btn btn-success">Edit</a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach

                    </tbody>

                </table>
            </div>

        </div>

        <div class="card-footer">
            <div class="row justify-content-between">
                <div class="col-md-6">
                    <p>Showing {{ 1+(($products->currentPage()-1)*2) }} to {{ $i-1 }} out of {{ $products->total() }}</p>
                </div>
                <div class="col-md-2">
                    {{ $products->links() }}
                </div>
            </div>
        </div>
    </div>

@endsection
