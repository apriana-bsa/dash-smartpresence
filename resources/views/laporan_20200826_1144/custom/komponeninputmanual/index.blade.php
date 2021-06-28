@extends('layouts.master')
@section('title', trans('all.laporankomponeninputmanual'))
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
  td{
    padding:5px;
  }

  .dataTables_wrapper{
    padding-bottom:0;
  }
  </style>
  <div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
      <h2>{{ trans('all.laporankomponeninputmanual') }}</h2>
      <ol class="breadcrumb">
        <li>{{ trans('all.laporan') }}</li>
        <li>{{ trans('all.custom') }}</li>
        <li class="active"><strong>{{ trans('all.laporankomponeninputmanual') }}</strong></li>
      </ol>
    </div>
    <div class="col-lg-2">

    </div>
  </div>

  <div class="wrapper wrapper-content animated fadeIn">
    <div class="row">
        <div class="col-lg-12">
        <form action="{{ url('laporan/custom/komponeninputmanual/nextstep') }}" method="post">
            {{ csrf_field() }}
            <table width="100%">
                <tr>
                    <td style="float:left;margin-top:8px">{{ trans('all.periode') }}</td>
                    <td style="float:left">
                        <select class="form-control" id="bulan" name="bulan">
                            <option value="01" @if($bulansekarang == "01") selected @endif>{{ trans('all.januari') }}</option>
                            <option value="02" @if($bulansekarang == "02") selected @endif>{{ trans('all.februari') }}</option>
                            <option value="03" @if($bulansekarang == "03") selected @endif>{{ trans('all.maret') }}</option>
                            <option value="04" @if($bulansekarang == "04") selected @endif>{{ trans('all.april') }}</option>
                            <option value="05" @if($bulansekarang == "05") selected @endif>{{ trans('all.mei') }}</option>
                            <option value="06" @if($bulansekarang == "06") selected @endif>{{ trans('all.juni') }}</option>
                            <option value="07" @if($bulansekarang == "07") selected @endif>{{ trans('all.juli') }}</option>
                            <option value="08" @if($bulansekarang == "08") selected @endif>{{ trans('all.agustus') }}</option>
                            <option value="09" @if($bulansekarang == "09") selected @endif>{{ trans('all.september') }}</option>
                            <option value="10" @if($bulansekarang == "10") selected @endif>{{ trans('all.oktober') }}</option>
                            <option value="11" @if($bulansekarang == "11") selected @endif>{{ trans('all.november') }}</option>
                            <option value="12" @if($bulansekarang == "12") selected @endif>{{ trans('all.desember') }}</option>
                        </select>
                    </td>
                    <td style="float:left">
                        <select class="form-control" id="tahun" name="tahun">
                            <option value="{{ substr($tahundropdown->tahun1, 2) }}" @if($tahunsekarang == substr($tahundropdown->tahun1, 2)) selected @endif>{{ $tahundropdown->tahun1 }}</option>
                            <option value="{{ substr($tahundropdown->tahun2, 2) }}" @if($tahunsekarang == substr($tahundropdown->tahun2, 2)) selected @endif>{{ $tahundropdown->tahun2 }}</option>
                            <option value="{{ substr($tahundropdown->tahun3, 2) }}" @if($tahunsekarang == substr($tahundropdown->tahun3, 2)) selected @endif>{{ $tahundropdown->tahun3 }}</option>
                            <option value="{{ substr($tahundropdown->tahun4, 2) }}" @if($tahunsekarang == substr($tahundropdown->tahun4, 2)) selected @endif>{{ $tahundropdown->tahun4 }}</option>
                            <option value="{{ substr($tahundropdown->tahun5, 2) }}" @if($tahunsekarang == substr($tahundropdown->tahun5, 2)) selected @endif>{{ $tahundropdown->tahun5 }}</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td colspan="3" style="float:left">
                        <button type="submit" class="btn btn-primary"><i class="fa fa-check-circle"></i>&nbsp;&nbsp;{{ trans('all.tampilkan') }}</button>&nbsp;&nbsp;
                        <button type="button" onclick="ke('laporankomponeninputmanual/setulang')" class="btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.setulang') }}</button>&nbsp;&nbsp;
                    </td>
                </tr>
            </table>
            @if(count($atributs) > 0)
                <p><b>{{ trans('all.atribut') }}</b></p>
            @endif
            @foreach($atributs as $atribut)
                @if(count($atribut->atributnilai) > 0)
                    <div class="col-md-4">
                        <input type="checkbox" id="semuaatribut_{{ $atribut->id }}" onclick="checkboxallclick('semuaatribut_{{ $atribut->id }}','attr_{{ $atribut->id }}')">&nbsp;&nbsp;
                        <span class="spancheckbox" onclick="spanallclick('semuaatribut_{{ $atribut->id }}','attr_{{ $atribut->id }}')">{{ $atribut->atribut }}</span>
                        <br>
                        @foreach($atribut->atributnilai as $atributnilai)
                            @if(Session::has('laporankomponeninputmanual_atribut'))
                                {{ $checked = false }}
                                @for($i=0;$i<count(Session::get('laporankomponeninputmanual_atribut'));$i++)
                                    @if($atributnilai->id == Session::get('laporankomponeninputmanual_atribut')[$i])
                                        <span style="display:none">{{ $checked = true }}</span>
                                    @endif
                                @endfor
                                <div style="padding-left:15px">
                                    <table>
                                        <tr>
                                            <td valign="top" style="width:10px;">
                                                <input type="checkbox" class="attr_{{ $atribut->id }}" onchange="checkAllAttr('attr_{{ $atribut->id }}','semuaatribut_{{ $atribut->id }}')" id="atributnilai_{{ $atributnilai->id }}" @if($checked == true) checked @endif name="atributnilai[]" value="{{ $atributnilai->id }}">
                                            </td>
                                            <td valign="top">
                                                <span class="spancheckbox" onclick="spanClick('atributnilai_{{ $atributnilai->id }}')">{{ $atributnilai->nilai }}</span>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            @else
                                <div style="padding-left:15px">
                                    <table>
                                        <tr>
                                            <td valign="top" style="width:10px;">
                                                <input type="checkbox" class="attr_{{ $atribut->id }}" onchange="checkAllAttr('attr_{{ $atribut->id }}','semuaatribut_{{ $atribut->id }}')" id="atributnilai_{{ $atributnilai->id }}" name="atributnilai[]" value="{{ $atributnilai->id }}">
                                            </td>
                                            <td valign="top" style="padding-left:10px">
                                                <span class="spancheckbox" onclick="spanClick('atributnilai_{{ $atributnilai->id }}')">{{ $atributnilai->nilai }}</span>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endif
            @endforeach
        </form>
      </div>
    </div>
  </div>

@stop