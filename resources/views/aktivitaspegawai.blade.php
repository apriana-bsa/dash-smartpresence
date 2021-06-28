<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Aktivitas</title>

    <link rel="stylesheet" href="{{ asset('lib/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('lib/font-awesome/css/font-awesome.css') }}">

    <!-- Toastr style -->
    <link rel="stylesheet" href="{{ asset('lib/css/plugins/toastr/toastr.min.css') }}">
    <link rel="stylesheet" href="{{ asset('lib/css/plugins/blueimp/css/blueimp-gallery.min.css') }}">
    <link rel="stylesheet" href="{{ asset('lib/css/animate.css') }}">
    <link rel="stylesheet" href="{{ asset('lib/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('/lib/flag-icon-css/css/flag-icon.min.css') }}" type="text/css" />

    <link rel="stylesheet" type="text/css" media="screen" href="{{ asset('/lib/css/bootstrap-datetimepicker.min.css') }}">
    <link rel="stylesheet" type="text/css" media="screen" href="{{ asset('/lib/css/button_loading.css') }}">
    <link rel="stylesheet" href="{{ asset('/lib/css/style-custom.css') }}" type="text/css" />
    <link rel="stylesheet" href="{{ asset('/lib/css/plugins/iCheck/custom.css') }}" type="text/css" />
    <link rel="stylesheet" href="{{ asset('/lib/css/style.css') }}" type="text/css" />
    <link rel="stylesheet" href="{{ asset('/lib/css/sweetalert2.css') }}" type="text/css" />
    <link rel="stylesheet" type="text/css" href="{{ asset('/lib/css/dataTables.bootstrap.css') }}">
    {{--<link rel="stylesheet" type="text/css" href="{{ asset('/lib/css/dataTables.min.css') }}">--}}
    <link rel="stylesheet" href="{{ asset('lib/css/token-input-facebook.css') }}" type="text/css" />
    <link rel="stylesheet" type="text/css" href="{{ asset('lib/css/iconselect.css') }}" >
    <link rel="stylesheet" href="{{ asset('lib/css/rangecalendar.css') }}" type="text/css" media="screen">
    <link rel="stylesheet" type="text/css" media="screen" href="{{ asset('lib/css/typeaheadjs.css') }}">
    <link rel="stylesheet" type="text/css" media="screen" href="{{ asset('lib/css/BootSideMenu.css') }}">
    <link rel="stylesheet" href="{{ asset('lib/css/plugins/blueimp/css/blueimp-gallery.min.css') }}" type="text/css" media="screen">
    <link rel="stylesheet" href="{{ asset('lib/css/plugins/blueimp/css/blueimp-rotate.css') }}" type="text/css" media="screen">
    <link rel="stylesheet" type="text/css" media="screen" href="{{ asset('lib/css/jquery.simplecolorpicker.css') }}">
    <link rel="stylesheet" type="text/css" media="screen" href="{{ asset('lib/css/jquery.simplecolorpicker-regularfont.css') }}">
    <link rel="stylesheet" type="text/css" media="screen" href="{{ asset('lib/css/jquery.simplecolorpicker-glyphicons.css') }}">
    <link rel="stylesheet" type="text/css" media="screen" href="{{ asset('lib/css/jquery.simplecolorpicker-fontawesome.css') }}">
    <link rel="stylesheet" href="{{ asset('lib/css/select2.min.css') }}" />

    <!-- Mainly scripts -->
    <script src="{{ asset('lib/js/jQuery-2.1.4.min.js') }}"></script>
    <script src="{{ asset('lib/js/jquery.simplecolorpicker.js') }}"></script>
    <script src="{{ asset('lib/js/plugins/iCheck/icheck.min.js') }}"></script>
    <script src="{{ asset('lib/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('lib/js/plugins/metisMenu/jquery.metisMenu.js') }}"></script>
    <script src="{{ asset('lib/js/plugins/slimscroll/jquery.slimscroll.min.js') }}"></script>

    <!-- Custom and plugin javascript -->
    <script src="{{ asset('lib/js/inspinia.js') }}"></script>
    <script src="{{ asset('lib/js/plugins/pace/pace.min.js') }}"></script>

    <script type="text/javascript" src="{{ asset('lib/js/jquery-ui.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('lib/js/jquery.ui.touch-punch.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('lib/js/moment+langs.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('lib/js/jquery.rangecalendar.js') }}"></script>
    <script src="{{ asset('lib/js/plugins/blueimp/jquery.blueimp-gallery.min.js') }}"></script>
    <script src="{{ asset('lib/js/plugins/blueimp/blueimp-rotate.js') }}"></script>

    <!-- Toastr script -->
    <script src="{{ asset('lib/js/plugins/toastr/toastr.min.js') }}"></script>
    <script src="{{ asset('/lib/js/jquery.mask.min.js') }}"></script>
    <script src="{{ asset('/lib/js/bootstrap-datetimepicker.min.js') }}"></script>
    <script src="{{ asset('/lib/js/sweetalert2.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/lib/js/dataTables.min.js') }}"></script>
    <script type="text/javascript" language="javascript" src="{{ asset('/lib/js/dataTables.bootstrap.js') }}"></script>
    <script src="{{ asset('/lib/js/BootSideMenu.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/lib/js/jquery.tokeninput.js') }}"></script>
    <script src="{{ asset('/lib/js/jquery.inputmask.js') }}"></script>
    <script src="{{ asset('/lib/js/jquery.inputmask.date.extensions.js') }}"></script>
    <script type="text/javascript" src="{{ asset('lib/js/iconselect.js') }}"></script>
    <script src="{{ asset('lib/js/bootstrap-filestyle.js') }}"></script>
    <script src="{{ asset('lib/js/typeahead.min.js') }}"></script>
    <script src="{{ asset('lib/js/select2.min.js') }}"></script>
    <script type='text/javascript' src="{{ asset('/lib/js/util.js') }}"></script>
    <style>
        .toast-info{
            top:20px;
        }
    </style>
    <script>
        @if(Session::get('message'))
        $(document).ready(function() {
            setTimeout(function() {
                toastr.options = {
                    closeButton: true,
                    progressBar: true,
                    timeOut: 5000,
                    extendedTimeOut: 5000,
                    positionClass: 'toast-top-center'
                };
                toastr.info('{{ Session::get("message") }}', 'Informasi');
            }, 500);
        });
        @endif

        @if(isset($dataaktivitas))
            $(function(){
                $('.date').datepicker({ format: "dd/mm/yyyy" }).on('changeDate', function(ev){
                    $(this).datepicker('hide');
                });
                $('.date').mask("00/00/0000", {clearIfNotMatch: true});
                $('.jam').inputmask( 'h:s' );

                $("#simpan").click(function () {
                    @foreach($dataaktivitas as $key)
                        @if($key->jenisinputan == 'checkbox' && $key->harusdiisi == 'y')
                            var isempty = false;
                            var nonempty = $('.checkbox_{{$key->id}}').filter(function() {
                                return this.checked;
                            });
                            if(nonempty.length === 0){
                                isempty = true;
                            }
                            if(!isempty){
                                $('.checkbox_{{$key->id}}').removeAttr('required');
                            }
                        @endif
                    @endforeach

                    $('#submit').trigger('click');
                })
            });

            function numberCheck(e){
                // console.log($(e).val(), isNumeric($(e).val()));
                if($(e).val() != ''){
                    if(!isNumeric($(e).val())){
                        $(e).val('');
                    }
                }
            }
        @endif
    </script>
</head>
<body id="mainbody" style="background-color: #f3f3f4">
    <div class="row">
        <div id='loading-saver' style='display:none;position:absolute;color:white;background: rgba(0, 0, 0, 0);width:100%;height:100%;z-index:99999999999999;'><span style="width:100%;position:absolute;margin:20% 0%"></span></div>
        <div id='loading-saver-withspinner' style='display:none;position:absolute;color:white;background: rgba(0.5, 0.5, 0.5, 0.5);width:100%;height:100%;z-index:99999999999999;'><span style="width:100%;position:absolute;margin:20% 50%;font-size:50px"><i class="fa fa-spinner fa-spin"></i></span></div>
        <div class="wrapper wrapper-content animated fadeIn">
            <div class="col-lg-12">
                <div class="ibox float-e-margins">
                    <div class="ibox-content">
                        <center>
                            <h3>Aktivitas Pegawai ({{$namapegawai}}) Tanggal {{date('d/m/Y')}}</h3>
                        </center>
                    </div>
                </div>
            </div>
            @if(isset($errormsg))
                <div class="col-lg-12">
                    <div class="ibox float-e-margins">
                        <div class="ibox-content">
                            <center><h4>{{$errormsg}}</h4></center>
                        </div>
                    </div>
                </div>
            @endif
            @if(isset($dataaktivitaskategori))
                <form id="formaktivitas" method="post" action="{{url('setaktivitaskategori')}}">
                    {{csrf_field()}}
                    <input type="hidden" name="idperusahaan" value="{{$idperusahaan}}">
                    <input type="hidden" name="idpegawai" value="{{$idpegawai}}">
                    <div class="col-lg-12">
                        <div class="ibox float-e-margins">
                            <div class="ibox-content">
                                <h4>Kategori</h4>
                                <select class="form-control" required name="aktivitaskategori">
                                    <option value=""></option>
                                    @foreach($dataaktivitaskategori as $key)
                                        <option value="{{$key->id}}">{{$key->nama}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <div class="ibox float-e-margins">
                            <div class="ibox-content">
                                <center>
                                    <button type="submit" class="btn btn-primary"><i class="fa fa-check"></i>&nbsp;&nbsp;Lanjut</button>
                                </center>
                            </div>
                        </div>
                    </div>
                </form>
            @endif
            @if(isset($dataaktivitas))
                <form id="formaktivitas" method="post" action="{{url('aktivitaspegawai')}}">
                    {{csrf_field()}}
                    <input type="hidden" name="idperusahaan" value="{{$idperusahaan}}">
                    <input type="hidden" name="idpegawai" value="{{$idpegawai}}">
                    <input type="hidden" name="idaktivitaskategori" value="{{$idaktivitaskategori}}">
                    @foreach($dataaktivitas as $key)
                        <input type="hidden" name="idaktivitas[]" value="{{$key->id}}">
                        <input type="hidden" name="jenisinputan[]" value="{{$key->jenisinputan}}">
                        <input type="hidden" name="pertanyaan[]" value="{{$key->pertanyaan}}">
                        <div class="col-lg-12">
                            <div class="ibox float-e-margins">
                                <div class="ibox-content">
                                    <h4>{{$key->pertanyaan}}</h4>
                                    @if($key->jenisinputan == 'karakter')
                                        <input type="text" @if($key->harusdiisi == 'y') required @endif class="form-control" id="karakter_{{$key->id}}"  name="karakter_{{$key->id}}" maxlength="{{$key->panjangkarakter}}">
                                    @endif
                                    @if($key->jenisinputan == 'karakterpanjang')
                                        <textarea @if($key->harusdiisi == 'y') required @endif class="form-control" rows="4" id="karakterpanjang_{{$key->id}}" name="karakterpanjang_{{$key->id}}" maxlength="{{$key->panjangkarakter}}"></textarea>
                                    @endif
                                    @if($key->jenisinputan == 'angka')
                                        <input type="text" @if($key->harusdiisi == 'y') required @endif onkeyup="return numberCheck(this)" onkeypress="return onlyNumber(0,event)" class="form-control" id="angka_{{$key->id}}" name="angka_{{$key->id}}">
                                    @endif
                                    @if($key->jenisinputan == 'desimal')
                                        <input type="text" @if($key->harusdiisi == 'y') required @endif onkeyup="return numberCheck(this)" onkeypress="return onlyNumber(2,event)" class="form-control" id="desimal_{{$key->id}}" name="desimal_{{$key->id}}">
                                    @endif
                                    @if($key->jenisinputan == 'tanggaldanjam')
                                        <div class="row">
                                            <div class="col-xs-9">
                                                <input value="{{date('d/m/Y')}}" type="text" @if($key->harusdiisi == 'y') required @endif class="form-control date" id="tanggaldanjam_tgl_{{$key->id}}" name="tanggaldanjam_tgl_{{$key->id}}">
                                            </div>
                                            <div class="col-xs-3" style="padding-left:0">
                                                <input value="{{date('H:m')}}" type="text" @if($key->harusdiisi == 'y') required @endif class="form-control jam" id="tanggaldanjam_jam_{{$key->id}}" name="tanggaldanjam_jam_{{$key->id}}">
                                            </div>
                                        </div>
                                    @endif
                                    @if($key->jenisinputan == 'tanggal')
                                        <input value="{{date('d/m/Y')}}" type="text" @if($key->harusdiisi == 'y') required @endif class="form-control date" id="tanggal_{{$key->id}}" name="tanggal_{{$key->id}}">
                                    @endif
                                    @if($key->jenisinputan == 'jam')
                                        <input value="{{date('H:m')}}" type="text" @if($key->harusdiisi == 'y') required @endif class="form-control jam" id="jam_{{$key->id}}" name="jam_{{$key->id}}">
                                    @endif
                                    @if($key->jenisinputan == 'checkbox' ||$key->jenisinputan == 'radiobutton' ||$key->jenisinputan == 'combobox')
                                        @foreach($dataaktivitasmultiple as $multiple)
                                            @if($multiple->idaktivitas == $key->id)
                                                @if($key->jenisinputan == 'checkbox')
                                                    <input @if($key->harusdiisi == 'y') required @endif type="checkbox" value="{{$multiple->keterangan}}" class="checkbox_{{$key->id}}" id="{{str_replace(" ", "", $multiple->keterangan)}}" name="checkbox_{{$key->id}}[]">&nbsp;&nbsp;<span onclick="spanClick('{{str_replace(" ", "", $multiple->keterangan)}}')">{{$multiple->keterangan}}</span><p></p>
                                                @endif
                                                @if($key->jenisinputan == 'radiobutton')
                                                    <input @if($key->harusdiisi == 'y') required @endif type="radio" value="{{$multiple->keterangan}}" id="{{str_replace(" ", "", $multiple->keterangan)}}" name="radiobutton_{{$key->id}}">&nbsp;&nbsp;<span onclick="spanClick('{{str_replace(" ", "", $multiple->keterangan)}}')">{{$multiple->keterangan}}</span><p></p>
                                                @endif
                                            @endif
                                        @endforeach
                                        @if($key->jenisinputan == 'combobox')
                                            <select class="form-control" @if($key->harusdiisi == 'y') required @endif id="combobox_{{$key->id}}" name="combobox_{{$key->id}}">
                                                @foreach($dataaktivitasmultiple as $multiple)
                                                    @if($multiple->idaktivitas == $key->id)
                                                        <option value="{{$multiple->keterangan}}">{{$multiple->keterangan}}</option>
                                                    @endif
                                                @endforeach
                                            </select>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                    <div class="col-lg-12">
                        <div class="ibox float-e-margins">
                            <div class="ibox-content">
                                <center>
{{--                                    <button type="button" onclick="ke('{{url('aktivitaspegawai/'.$idperusahaan.'/'.$idpegawai)}}')" class="btn btn-primary"><i class="fa fa-chevron-left"></i>&nbsp;&nbsp;Kembali</button>&nbsp;&nbsp;--}}
                                    <button type="button" id="simpan" class="btn btn-primary"><i class="fa fa-check"></i>&nbsp;&nbsp;Submit</button>
                                    <button type="submit" id="submit" style="display:none" class="btn btn-primary"></button>
                                </center>
                            </div>
                        </div>
                    </div>
                </form>
            @endif
        </div>
    </div>
</body>
</html>