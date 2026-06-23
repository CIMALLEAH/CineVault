@extends('layouts.app')
@section('title', 'Add Movie')
@section('page-title', 'Add New Movie')
 
@section('content')
<div style="max-width:700px;">
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.movies.store') }}" enctype="multipart/form-data">
                @csrf
                @include('admin.movies._form')
                <div style="display:flex; gap:10px; margin-top:20px;">
                    <button type="submit" class="btn btn-primary">
                        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                        Save Movie
                    </button>
                    <a href="{{ route('admin.movies.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection