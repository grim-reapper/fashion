@extends('layouts.app')

@section('content')

    <section class="shop spad">
        <div class="container">
            <div class="row">
                <div class="col-lg-3 col-md-3">
                    <div class="shop__sidebar">
                        <form action="{{ route('shop') }}" method="get">
                            <div class="sidebar__categories">
                                <div class="section-title">
                                    <h4>Categories</h4>
                                </div>
                                <div class="categories__accordion">
                                    <div class="accordion" id="accordionExample">

                                        @foreach($categories as $category)

                                            <div class="card">
{{--                                                <div class=" active">--}}
                                                    <a href="{{ route('shop', ['category' => $category->id]) }}">{{ $category->category_name }}</a>
{{--                                                </div>--}}
{{--                                                <div id="collapseOne" class="collapse show" data-parent="#accordionExample">--}}
{{--                                                    <div class="card-body">--}}
{{--                                                        <ul>--}}
{{--                                                            <li><a href="#">Coats</a></li>--}}
{{--                                                            <li><a href="#">Jackets</a></li>--}}
{{--                                                            <li><a href="#">Dresses</a></li>--}}
{{--                                                            <li><a href="#">Shirts</a></li>--}}
{{--                                                            <li><a href="#">T-shirts</a></li>--}}
{{--                                                            <li><a href="#">Jeans</a></li>--}}
{{--                                                        </ul>--}}
{{--                                                    </div>--}}
{{--                                                </div>--}}
                                            </div>
                                        @endforeach

                                    </div>
                                </div>
                            </div>

                            <div class="sidebar__filter">
                                <div class="section-title">
                                    <h4>Shop by price</h4>
                                </div>
                                <div class="filter-range-wrap">
                                    <div class="price-range ui-slider ui-corner-all ui-slider-horizontal ui-widget ui-widget-content" data-min="0" data-max="500"></div>
                                    <div class="range-slider">
                                        <div class="price-input">
                                            <p>Price:</p>
                                            <input type="text" id="minamount">
                                            <input type="text" id="maxamount">
                                        </div>
                                    </div>
                                </div>
                                <a href="#">Filter</a>
                            </div>

                            <div class="sidebar__sizes">
                                <div class="section-title">
                                    <h4>Shop by size</h4>
                                </div>
                                <div class="size__list">

                                    @foreach($sizes as $size)
                                        <label for="{{ $size->name }}">
                                            {{ $size->name }}
                                            <input type="checkbox" id="{{ $size->name }}" value="{{ $size->id }}">
                                            <span class="checkmark"></span>
                                        </label>
                                    @endforeach

                                </div>
                            </div>

                            <div class="sidebar__color">
                            <div class="section-title">
                                <h4>Shop by color</h4>
                            </div>
                            <div class="size__list color__list">

                                @foreach($colors as $color)
                                    <label for="{{ $color->name }}">
                                        {{ $color->name }}
                                        <input type="checkbox" id="{{ $color->name }}" value="{{ $color->id }}">
                                        <span class="checkmark"></span>
                                    </label>
                                @endforeach

                            </div>
                        </div>
                        </form>
                    </div>
                </div>

                <div class="col-lg-9 col-md-9">
                    <div class="row">

                        @if($products)

                            @foreach($products as $product)

                                <div class="col-lg-4 col-md-6">
                                    <div class="product__item">
                                        <a href="{{ route('detail', ['product_id' => $product->product_id ]) }}">
                                        <div class="product__item__pic set-bg" data-setbg="{{ asset(config('custom.product_img_path'). $product->image) }}">
                                            @if($product->summary)
                                                <div class="label new">{{ $product->summary ? $product->summary : '' }}</div>
                                            @endif
                                        </div>
                                        </a>
                                        <div class="product__item__text">
                                            <h6><a href="#">{{ $product->product_name }}</a></h6>
{{--                                            <div class="rating">--}}
{{--                                                <i class="fa fa-star"></i>--}}
{{--                                                <i class="fa fa-star"></i>--}}
{{--                                                <i class="fa fa-star"></i>--}}
{{--                                                <i class="fa fa-star"></i>--}}
{{--                                                <i class="fa fa-star"></i>--}}
{{--                                            </div>--}}
                                            <div class="product__price">&pound; {{ number_format($product->price, 2) }}</div>
{{--                                            <a class="site-btn" href="" onclick="addToBasket()">Add to cart</a>--}}

                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="col-lg-4 col-md-6">
                                <p>Sorry! no item found</p>
                            </div>

                        @endif


                        <div class="col-lg-12 text-center">
                            <div class="pagination__option">
                                {{ $products->links() }}
{{--                                <a href="#">1</a>--}}
{{--                                <a href="#">2</a>--}}
{{--                                <a href="#">3</a>--}}
{{--                                <a href="#"><i class="fa fa-angle-right"></i></a>--}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

<script>
	function addToCart(e, product_id) {
		e.preventDefault();
		axios({
			method: 'post',
			url: '{{ route('add-to-cart') }}',
			data: {
				'product_id': product_id
			}
		}).then((response) => {
				if (response.data) {
					console.log('Product added to cart')
					{{--window.location = '{{ route('cart') }}';--}}
				} else {
					console.log('Sorry there is some problem please contact us');
				}
			})
			.catch((error) => {
				console.log(error);
			});
	}
</script>

<style>
    .sidebar__sizes .size__list label, .sidebar__color .size__list label{
        text-transform: none !important;
    }
</style>