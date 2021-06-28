@extends('layouts.master')
@section('title', trans('all.slipgaji'))
@section('content')
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
            <h2>{{ trans('all.slipgaji').' ('.$slipgaji.')' }}</h2>
            <ol class="breadcrumb">
                <li>{{ trans('all.datainduk') }}</li>
                <li>{{ trans('all.payroll') }}</li>
                <li>{{ trans('all.slipgaji') }}</li>
                <li class="active"><strong>{{ trans('all.komponenmaster') }}</strong></li>
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
            <div class="col-lg-12" style="margin-bottom:20px">
                <button type="button" onclick="simpan()" class="ladda-button btn btn-primary slide-left"><span class="label2"><i class='fa fa-save'></i>&nbsp;&nbsp;{{ trans('all.simpan') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
                <button type="button" onclick="ke('{{url('datainduk/payroll/slipgaji')}}')" class="btn btn-primary"><i class='fa fa-undo'></i>&nbsp;&nbsp;{{ trans('all.kembali') }}</button>
            </div>
            <div class="col-lg-6">
                <div class="ibox float-e-margins">
                    <div class="ibox-content">
                        <h2>{{ trans('all.tersedia') }}</h2>
                        <ul id="foo" class="block__list block__list_words">
                            @if($datakomponenmaster != '')
                                @foreach($datakomponenmaster as $key)
                                    <li>
                                        <input type="hidden" value="{{ $key->id}}" name="idslipgaji[]">
                                        <table>
                                            <tr>
                                                <td><span class="drag-handle">&#9776;</span></td>
                                                <td>{{ $key->nama }}</td>
                                            </tr>
                                        </table>
                                    </li>
                                @endforeach
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
            <form method="post" action="">
                {{ csrf_field() }}
                <div class="col-lg-6">
                    <div class="ibox float-e-margins">
                        <div class="ibox-content">
                            <h2>{{ trans('all.tersimpan') }}</h2>
                            <ul id="bar" class="block__list block__list_words">
                                @if($datakomponenmasterslipgaji != '')
                                    @foreach($datakomponenmasterslipgaji as $key)
                                        <li>
                                            <input type="hidden" value="{{ $key->id }}" name="idslipgaji[]">
                                            <table>
                                                <tr>
                                                    <td><span class="drag-handle">&#9776;</span></td>
                                                    <td>{{ $key->nama }}</td>
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
                    <button id="submit" type="submit" class="ladda-button btn btn-primary slide-left"><span class="label2"><i class='fa fa-save'></i>&nbsp;&nbsp;{{ trans('all.simpan') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
                    <button type="button" onclick="ke('{{url('datainduk/payroll/slipgaji')}}')" class="btn btn-primary"><i class='fa fa-undo'></i>&nbsp;&nbsp;{{ trans('all.kembali') }}</button>
                </div>
            </form>
        </div>
    </div>
    <link href="{{ asset('lib/css/appSortable.css') }}" rel="stylesheet" type="text/css" />
    <script src="{{ asset('lib/js/Sortable.js') }}"></script>
    <script src="{{ asset('lib/js/appSortable.js') }}"></script>
    <script>
        function simpan(){
            $("#submit").trigger('click');
        }
    </script>
@stop