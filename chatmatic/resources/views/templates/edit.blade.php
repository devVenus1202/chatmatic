@extends('layouts.app')

@section('title', 'Workflows - Chatmatic Admin')

@section('content')
    <div class="row py-4">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                    <li class="breadcrumb-item"><a href="/templates">Workflow Templates</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Edit Template</li>
                </ol>
            </nav>
            <h3>Workflow Templates</h3>
        </div>
    </div>
    <div class="row">
        <div class="col">
			<form action="/template/{!! $template->uid !!}/update" method="post">
				{{ csrf_field() }}
				
				<div class="form-group">
					<label for="exampleInputEmail1">Name</label>
                	<input type="text" class="form-control" name="name" required="required" value="{!! $template->name !!}">
                </div>

                <div class="form-group">
					<label for="exampleInputEmail1">Category</label>
                	<select class="form-control" name="category" required="required">
                		<option value="{!! $template->category !!}">{!! $template->category !!}</option>
                		<option value="Ecommerce">Ecommerce</option>
                		<option value="Digital Products">Digital Products</option>
                		<option value="Health & Fitness">Health & Fitness</option>
                		<option value="Local Business">Local Business</option>
                		<option value="General">General</option>
					</select>
                </div>

                <div class="form-group">
					<label for="exampleInputEmail1">Description</label>
                	<textarea class="form-control" rows="5" name="description" required="required">{!! $template->description !!}</textarea>
                </div>

                <div class="form-group">
					<label for="exampleInputEmail1">Price</label>
                	<input type="number" class="form-control" name="price" required="required" value="{!! $template->price !!}" min=0 step="0.01">
                </div>

                <button type="submit" class="btn btn-primary mb-2">Update</button>
            </form>            
        </div>
    </div>
@endsection




