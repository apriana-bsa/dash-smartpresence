@extends('layouts/master')
@section('title', trans('all.customtv'))
@section('content')

    <!-- Switchery -->
    <link href="{{ asset('lib/css/plugins/switchery/switchery.css') }}" rel="stylesheet">
    <script src="{{ asset('lib/js/plugins/switchery/switchery.js') }}"></script>
    <!-- NouSlider -->
    <script src="{{ asset('lib/js/plugins/nouslider/jquery.nouislider.min.js') }}"></script>
    <link href="{{ asset('lib/css/plugins/nouslider/jquery.nouislider.css') }}" rel="stylesheet">
    <script src="{{ asset('lib/js/jscolor/jscolor.js') }}"></script>
    <script>
        $(function(){

            @if(Session::get('message'))
                setTimeout(function() {
                toastr.options = {
                    closeButton: true,
                    progressBar: true,
                    timeOut: 4000,
                    extendedTimeOut: 4000,
                    positionClass: 'toast-bottom-right'
                };
                toastr.info('{{ Session::get("message") }}', '{{ trans("all.pemberitahuan") }}');
            }, 500);
            @endif
        });

        function validasi(){

            $('#submit').attr( 'data-loading', '' );
            $('#submit').attr('disabled', 'disabled');
            $('#aturmesin').attr( 'data-loading', '' );
            $('#aturmesin').attr('disabled', 'disabled');
            $('#clear').attr('disabled', 'disabled');
        }
    </script>
    <style>
        .spanmesin{
            cursor: pointer;
        }
    </style>
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-10">
            <h2>{{ trans('all.customtv') }}</h2>
            <ol class="breadcrumb">
                <li>{{ trans('all.pengaturan') }}</li>
                <li class="active"><strong>{{ trans('all.customtv') }}</strong></li>
            </ol>
        </div>
        <div class="col-lg-2">

        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeIn">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox float-e-margins">
                    <div class="ibox-content p-md">
                        @if($data != '')
                            <form method="post" id='myform' action="" onsubmit="return validasi()">
                                {{ csrf_field() }}
                                <table cellpadding="0" width='100%' cellspacing="0" border="0">
                                    <tr>
                                        <td style='width:170px;padding:5px;'>{{ trans('all.header1') }}</td>
                                        <td style='padding:5px;'>
                                            <textarea name="header1" style="resize:none;width:480px" id="header1" class="form-control">{{ $data->header1 }}</textarea>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style='padding:5px;'>{{ trans('all.header2') }}</td>
                                        <td style='padding:5px;'>
                                            <textarea name="header2" style="resize:none;width:480px" id="header2" class="form-control">{{ $data->header2 }}</textarea>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style='padding:5px;'>{{ trans('all.bahasa') }}</td>
                                        <td style='padding:5px;float:left'>
                                            <select name="bahasa" class="form-control">
                                                <option value="id" @if($data->bahasa == 'id') selected @endif>Indonesia</option>
                                                <option value="en" @if($data->bahasa == 'en') selected @endif>English</option>
                                                <option value="cn" @if($data->bahasa == 'cn') selected @endif>中国</option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style='padding:5px;'>{{ trans('all.nip') }}</td>
                                        <td style='padding:5px;float:left'>
                                            <select name="atribut_nip" class="form-control">
                                                <option value="" @if($data->atribut_nip == "") selected @endif></option>
                                                @if($dataatributvariable != '')
                                                    @foreach($dataatributvariable as $key)
                                                        <option value="{{ $key->id }}" @if($data->atribut_nip == $key->id) selected @endif>{{ $key->atribut }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style='padding:5px;'>{{ trans('all.atribut_nip_caption') }}</td>
                                        <td style='padding:5px;float:left'>
                                            <input type="text" maxlength="100" autocomplete="off" name="atribut_nip_caption" id="atribut_nip_caption" class="form-control" value="{{ $data->atribut_nip_caption }}">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style='padding:5px;'>{{ trans('all.jabatan') }}</td>
                                        <td style='padding:5px;float:left'>
                                            <select name="atribut_jabatan" class="form-control">
                                                <option value="" @if($data->atribut_jabatan == "") selected @endif></option>
                                                @if($dataatribut != '')
                                                    @foreach($dataatribut as $key)
                                                        <option value="{{ $key->id }}" @if($data->atribut_jabatan == $key->id) selected @endif>{{ $key->atribut }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style='padding:5px;'>{{ trans('all.atribut_jabatan_caption') }}</td>
                                        <td style='padding:5px;float:left'>
                                            <input type="text" maxlength="100" autocomplete="off" name="atribut_jabatan_caption" id="atribut_jabatan_caption" class="form-control" value="{{ $data->atribut_jabatan_caption }}">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style='padding:5px;'>{{ trans('all.tampil_terlambat') }}</td>
                                        <td style='padding:5px;float:left'>
                                            <div class="onoffswitch">
                                                <input type="checkbox" class="onoffswitch-checkbox" name="tampil_terlambat" @if($data->tampil_terlambat == 'y') checked @endif id="tampil_terlambat">
                                                <label class="onoffswitch-label" for="tampil_terlambat">
                                                    <span class="onoffswitch-inner"></span>
                                                    <span class="onoffswitch-switch"></span>
                                                </label>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style='padding:5px;'>{{ trans('all.tampil_pulangawal') }}</td>
                                        <td style='padding:5px;float:left'>
                                            <div class="onoffswitch">
                                                <input type="checkbox" class="onoffswitch-checkbox" name="tampil_pulangawal" @if($data->tampil_pulangawal == 'y') checked @endif id="tampil_pulangawal">
                                                <label class="onoffswitch-label" for="tampil_pulangawal">
                                                    <span class="onoffswitch-inner"></span>
                                                    <span class="onoffswitch-switch"></span>
                                                </label>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style='padding:5px;'>{{ trans('all.tampil_ijintidakmasuk') }}</td>
                                        <td style='padding:5px;float:left'>
                                            <div class="onoffswitch">
                                                <input type="checkbox" class="onoffswitch-checkbox" name="tampil_ijintidakmasuk" @if($data->tampil_ijintidakmasuk == 'y') checked @endif id="tampil_ijintidakmasuk">
                                                <label class="onoffswitch-label" for="tampil_ijintidakmasuk">
                                                    <span class="onoffswitch-inner"></span>
                                                    <span class="onoffswitch-switch"></span>
                                                </label>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style='padding:5px;'>{{ trans('all.tampil_kehadiranterbaik') }}</td>
                                        <td style='padding:5px;float:left'>
                                            <div class="onoffswitch">
                                                <input type="checkbox" class="onoffswitch-checkbox" name="tampil_kehadiranterbaik" @if($data->tampil_kehadiranterbaik == 'y') checked @endif id="tampil_kehadiranterbaik">
                                                <label class="onoffswitch-label" for="tampil_kehadiranterbaik">
                                                    <span class="onoffswitch-inner"></span>
                                                    <span class="onoffswitch-switch"></span>
                                                </label>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style='padding:5px;'>{{ trans('all.tampil_belumabsen') }}</td>
                                        <td style='padding:5px;float:left'>
                                            <div class="onoffswitch">
                                                <input type="checkbox" class="onoffswitch-checkbox" name="tampil_belumabsen" @if($data->tampil_belumabsen == 'y') checked @endif id="tampil_belumabsen">
                                                <label class="onoffswitch-label" for="tampil_belumabsen">
                                                    <span class="onoffswitch-inner"></span>
                                                    <span class="onoffswitch-switch"></span>
                                                </label>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style='padding:5px;'>{{ trans('all.tampil_logabsen') }}</td>
                                        <td style='padding:5px;float:left'>
                                            <div class="onoffswitch">
                                                <input type="checkbox" class="onoffswitch-checkbox" name="tampil_logabsen" @if($data->tampil_logabsen == 'y') checked @endif id="tampil_logabsen">
                                                <label class="onoffswitch-label" for="tampil_logabsen">
                                                    <span class="onoffswitch-inner"></span>
                                                    <span class="onoffswitch-switch"></span>
                                                </label>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style='padding:5px;'>{{ trans('all.warna_background') }}</td>
                                        <td style='padding:5px;float:left'>
                                            <input type="text" class="form-control color" size="7" autocomplete="off" value="{{ $data->warna_background }}" name="warna_background" id="warna_background" maxlength="10">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style='padding:5px;'>{{ trans('all.warna_headerfooter') }}</td>
                                        <td style='padding:5px;float:left'>
                                            <input type="text" class="form-control color" size="7" autocomplete="off" value="{{ $data->warna_headerfooter }}" name="warna_headerfooter" id="warna_headerfooter" maxlength="10">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style='padding:5px;'>{{ trans('all.warna_headerfooter_text') }}</td>
                                        <td style='padding:5px;float:left'>
                                            <input type="text" class="form-control color" size="7" autocomplete="off" value="{{ $data->warna_headerfooter_text }}" name="warna_headerfooter_text" id="warna_headerfooter_text" maxlength="10">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style='padding:5px;'>{{ trans('all.warna_card') }}</td>
                                        <td style='padding:5px;float:left'>
                                            <input type="text" class="form-control color" size="7" autocomplete="off" value="{{ $data->warna_card }}" name="warna_card" id="warna_card" maxlength="10">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style='padding:5px;'>{{ trans('all.warna_card_text') }}</td>
                                        <td style='padding:5px;float:left'>
                                            <input type="text" class="form-control color" size="7" autocomplete="off" value="{{ $data->warna_card_text }}" name="warna_card_text" id="warna_card_text" maxlength="10">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <button id="submit" type="submit" class="ladda-button btn btn-primary slide-left"><span class="label2"><i class='fa fa-save'></i>&nbsp;&nbsp;{{ trans('all.simpan') }}</span> <span class="spinner"></span></button>
                                        </td>
                                    </tr>
                                </table>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop