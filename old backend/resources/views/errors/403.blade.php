@extends('errors::minimal')

@section('title', __('Forbidden'))
@section('code', '403')
@section('message')
    <h4>You Have No Access to visit This page</h4>
    <a style="font-size: 16px; color:rgb(14, 129, 129)" href="{{route('logout')}}">Click Here to Log Out</a>
@endsection
