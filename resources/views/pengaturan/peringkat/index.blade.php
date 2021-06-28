@extends('layouts.master')
@section('title', trans('all.'.$menu))
@section('content')

    @if(Session::get('message'))
        <script>
            $(document).ready(function() {
                setTimeout(function() {
                    toastr.options = {
                        closeButton: true,
                        progressBar: true,
                        timeOut: 5000,
                        extendedTimeOut: 5000,
                        positionClass: 'toast-bottom-right'
                    };
                    toastr.success('{{ Session::get("message") }}', '{{ trans("all.pemberitahuan") }}');
                }, 500);
            });
        </script>
    @endif

    <style>
        .block__list {
            max-width:100% !important;
        }

        .block__list_tags {
            padding-left:0 !important;
        }
    </style>
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-10">
            <h2>{{ trans('all.'.$menu) }}</h2>
            <ol class="breadcrumb">
                <li>{{ trans('all.pengaturan') }}</li>
                <li class="active"><strong>{{ trans('all.peringkat') }}</strong></li>
            </ol>
        </div>
        <div class="col-lg-2">

        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeIn">
        <div class="row">
            <div class="col-lg-12">
                <div class="alert alert-info">
                    <i class="fa fa-warning"></i>&nbsp;&nbsp;
                    {!! trans('all.keterangandraganddrop') !!}
                </div>
            </div>
            <div class="col-lg-6">
                <div class="ibox float-e-margins">
                    <div class="ibox-content">
                        <h2>{{ trans('all.tersedia') }}</h2>
                        <ul id="foo" class="block__list block__list_words">
                            @if($datatersedia != '')
                                @foreach($datatersedia as $key)
                                    <li>
                                        <input type="hidden" value="{{ $key->nama }}" name="nama[]">
                                        <table>
                                            <tr>
                                                <td><span class="drag-handle">&#9776;</span></td>
                                                <td>{{ trans('all.'.$key->nama) }}</td>
                                                <td style="padding-left:5px">
                                                    <select class="form-control" name="urutan[]" style="float:left">
                                                        <option value="asc" @if($key->order == 'asc') selected @endif>ASC</option>
                                                        <option value="desc" @if($key->order == 'desc') selected @endif>DESC</option>
                                                    </select>
                                                </td>
                                            </tr>
                                        </table>
                                    </li>
                                @endforeach
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
            <form method="post" action="" onsubmit="return freezeButton()">
                {{ csrf_field() }}
                <div class="col-lg-6">
                    <div class="ibox float-e-margins">
                        <div class="ibox-content">
                            <h2>{{ trans('all.tersimpan') }}</h2>
                            <ul id="bar" class="block__list block__list_words">
                                @if($dataterpakai != '')
                                    @foreach($dataterpakai as $key)
                                        <li>
                                            <input type="hidden" value="{{ $key->nama }}" name="nama[]">
                                            <table>
                                                <tr>
                                                    <td><span class="drag-handle">&#9776;</span></td>
                                                    <td>{{ trans('all.'.$key->nama) }}</td>
                                                    <td style="padding-left:5px">
                                                        <select class="form-control" name="urutan[]" style="float:left">
                                                            <option value="asc" @if($key->order == 'asc') selected @endif>ASC</option>
                                                            <option value="desc" @if($key->order == 'desc') selected @endif>DESC</option>
                                                        </select>
                                                    </td>
                                                </tr>
                                            </table>
                                        </li>
                                    @endforeach
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-lg-12" style="margin-bottom:20px">
                    <button id="hitungperingkat" type="button" onclick="return hitungPeringkat()" class="ladda-button btn btn-success slide-left"><span class="label2"><i class='fa fa-undo'></i>&nbsp;&nbsp;{{ trans('all.hitungperingkat') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
                    <button id="submit" type="submit" class="ladda-button btn btn-primary slide-left"><span class="label2"><i class='fa fa-save'></i>&nbsp;&nbsp;{{ trans('all.simpan') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
                </div>
            </form>
        </div>
    </div>
    <link href="{{ asset('lib/css/appSortable.css') }}" rel="stylesheet" type="text/css" />
    <script src="{{ asset('lib/js/Sortable.js') }}"></script>
    <script src="{{ asset('lib/js/appSortable.js') }}"></script>
    <script>
    function hitungPeringkat(){
        $.ajax({
            type: "GET",
            url: '{{ url('generatecsrftoken') }}',
            data: '',
            cache: false,
            success: function(token){
                var dataString = '_token='+token;
                $.ajax({
                    type: "POST",
                    url: '{{ url('pengaturan/peringkat/hitungperingkat') }}',
                    data: dataString,
                    cache: false,
                    success: function(html){
                        if(html['status'] == 'OK'){
                            alertSuccess(html['pesan'],function(){ window.location.href='{{ url('pengaturan/peringkat') }}'});
                        }else{
                            alertError(html['pesan'],function(){ window.location.href='{{ url('pengaturan/peringkat') }}'});
                        }
                    }
                });
            }
        });
    }
    </script>
@stop