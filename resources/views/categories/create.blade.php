@extends('layouts.app')

@section('content')
    <ol class="breadcrumb">
      <li class="breadcrumb-item">
         <a href="{{ route('categories.index') }}">Category</a>
      </li>
      <li class="breadcrumb-item active">Create</li>
    </ol>
     <div class="container-fluid">
          <div class="animated fadeIn">
              @include('layouts.errors')
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <i class="fa fa-plus-square-o fa-lg"></i>
                                <strong>Create Category</strong>
                            </div>
                            <div class="card-body">
                                {{ html()->form('POST', route('categories.store'))->attribute('accept-charset', 'UTF-8')->open() }}

                                   @include('categories.fields')

                                {{ html()->closeModelForm() }}
                            </div>
                        </div>
                    </div>
                </div>
           </div>
    </div>
@endsection
