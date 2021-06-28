@extends('layouts.master')
@section('title', trans('all.tvdetail'))
@section('content')

    <script>
        $(document).ready(function() {
            @if(Session::get('message'))
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
            @endif

$('#submit').click(function(){

                $('#submit').attr( 'data-loading', '' );
                $('#form1').submit();
                return true;

            });
        });
    </script>
    <style>
        .blueimp-gallery {
            background: rgba(0, 0, 0, 0.8);
        }

        .block__list {
            max-width: 100% !important;
        }

        .animated {
            -webkit-animation-fill-mode: none;
            animation-fill-mode: none;
        }
    </style>
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-10">
            <h2>{{ trans('all.tvdetail').' ('.$tv.')' }}</h2>
            <ol class="breadcrumb">
                <li>{{ trans('all.pengaturan') }}</li>
                <li>{{ trans('all.tvdetail') }}</li>
                <li class="active"><strong>{{ trans('all.detail') }}</strong></li>
            </ol>
        </div>
        <div class="col-lg-2">

        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeIn">
        <div class="ibox float-e-margins">
            <div class="row">
                <form id="form1" method="post" action="{{ url('pengaturan/tv/'.$idtv.'/detail') }}" onsubmit="return freezeButton()">
                    {{ csrf_field() }}
                    <div class="col-lg-6">
                        <div class="ibox-content">
                            <h3>{{ trans('all.tvdetail') }}</h3>
                            <ul id="foo" class="block__list block__list_tags">
                                @if($datatvdetail != '')
                                    @foreach($datatvdetail as $key)
                                        <li>
                                            <input type="hidden" value="{{ $key->id }}" name="idtvgroup[]">
                                            {{ $key->nama }}
                                        </li>
                                    @endforeach
                                @endif
                            </ul>
                        </div>
                    </div>
                </form>
                <div class="col-lg-6">
                    <div class="ibox-content">
                        <h3>{{ trans('all.tvgroup') }}</h3>
                        <ul id="bar" class="block__list block__list_tags">
                            @if($datatvgroup != '')
                                @foreach($datatvgroup  as $key)
                                    <li>
                                        <input type="hidden" value="{{ $key->id }}" name="idtvgroup[]">
                                        {{ $key->nama }}
                                    </li>
                                @endforeach
                            @endif
                        </ul>
                    </div>
                </div>
                <br><p></p>
                <div class="col-lg-12" style="margin-top:20px">
                    *{!!  trans('all.keterangandragtvdetail') !!}<p></p>
                    <button id="submit" type="button" class="ladda-button btn btn-primary slide-left"><span class="label2"><i class='fa fa-save'></i>&nbsp;&nbsp;{{ trans('all.simpan') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
                    <button class="btn btn-primary" id="kembali" onclick="return ke('../../tv')"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.kembali') }}</button>
                </div>
            </div>
        </div>
    </div>
    <link href="{{ asset('lib/css/appSortable.css') }}" rel="stylesheet" type="text/css" />
    <script src="{{ asset('lib/js/Sortable.js') }}"></script>
    <script src="{{ asset('lib/js/appSortable.js') }}"></script>
@stop