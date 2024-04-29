@extends('admin.master')
@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Product Add</h1>
                </div>
{{--                <div class="col-sm-6">--}}
{{--                    <ol class="breadcrumb float-sm-right">--}}
{{--                        <li class="breadcrumb-item"><a href="#">Home</a></li>--}}
{{--                        <li class="breadcrumb-item active">Project Add</li>--}}
{{--                    </ol>--}}
{{--                </div>--}}
            </div>
        </div>
    </section>
    <section class="content">
        @includeIf('admin.status-message')
        <form action="{{route('admin::product.store')}}" method="post" enctype="multipart/form-data">
            @csrf
        <div class="row">
            <div class="col-md-6">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">General</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="inputName">Product Name</label>
                            <input type="text" id="inputName" class="form-control" name="name">
                        </div>
                        <div class="form-group">
                            <label for="inputDescription">Product Description</label>
                            <textarea id="inputDescription" class="form-control" rows="4" name="description"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="inputStatus">Category</label>
                            <select id="inputStatus" class="form-control custom-select" name="category">
                                @foreach($categories as $category)
                                <option value="{{$category->id}}">{{$category->category_name}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="inputClientCompany">Price</label>
                            <input type="number" id="inputClientCompany" class="form-control" name="price">
                        </div>
                        <div class="form-group">
                            <label for="inputProjectLeader">Summary (New/Sale)</label>
                            <input type="text" id="inputProjectLeader" class="form-control" name="summary">
                        </div>
                    </div>

                </div>

            </div>
            <div class="col-md-6">
                <div class="card card-secondary">
                    <div class="card-header">
                        <h3 class="card-title">Other Attributes</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="exampleInputFile">File input</label>
                            <div class="input-group">
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="exampleInputFile" name="file">
                                    <label class="custom-file-label" for="exampleInputFile">Choose file</label>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputEstimatedBudget">Quantity</label>
                            <input type="number" id="inputEstimatedBudget" class="form-control" name="quantity">
                        </div>
                        <div class="form-group">
                            <label for="inputSpentBudget">Product ID</label>
                            <input type="number" id="inputSpentBudget" class="form-control" name="product_id">
                        </div>
                        <div class="form-group">
                            <label for="inputEstimatedDuration">Color</label>
                            <input type="text" id="inputEstimatedDuration" class="form-control" name="color">
                        </div>
                        <div class="form-group">
                            <label for="inputEstimatedDuration">Status</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="status" checked>
                                <label class="form-check-label">Active</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="status">
                                <label class="form-check-label">In-active</label>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <a href="{{route('admin::product.view')}}" class="btn btn-secondary">Cancel</a>
                <input type="submit" value="Create new Product" class="btn btn-success float-right">
            </div>
        </div>
        </form>
    </section>
@endsection
@push('scripts')
<script src="{{asset('admin/plugins/bs-custom-file-input/bs-custom-file-input.min.js')}}"></script>
<script>
    $(function () {
        bsCustomFileInput.init();
    });
</script>
@endpush