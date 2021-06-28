@extends('layouts.master')
@section('title', trans('all.cuti'))
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
  <div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
      <h2>{{ trans('all.cuti') }}</h2>
      <ol class="breadcrumb">
        <li>{{ trans('all.datainduk') }}</li>
        <li>{{ trans('all.absensi') }}</li>
        <li class="active"><strong>{{ trans('all.cuti') }}</strong></li>
      </ol>
    </div>
    <div class="col-lg-2">

    </div>
  </div>

  <div class="wrapper wrapper-content animated fadeIn">
    <div class="row">
        <div class="col-lg-12">
        <form action="" method="post">
            {{ csrf_field() }}
            <table>
                <tr>
                    <td style="float:left;margin-top:7px">{{ trans('all.tahun') }}</td>
                    <td style="float:left;padding-left:10px">
                        <select class="form-control" name="tahun" id="tahun" onchange="this.form.submit()">
                            @if(Session::has('cuti_tahun'))
                                <option value="{{ $tahun->tahun1 }}" @if(Session::get('cuti_tahun') == $tahun->tahun1) selected @endif>{{ $tahun->tahun1 }}</option>
                                <option value="{{ $tahun->tahun2 }}" @if(Session::get('cuti_tahun') == $tahun->tahun2) selected @endif>{{ $tahun->tahun2 }}</option>
                                <option value="{{ $tahun->tahun3 }}" @if(Session::get('cuti_tahun') == $tahun->tahun3) selected @endif>{{ $tahun->tahun3 }}</option>
                                <option value="{{ $tahun->tahun4 }}" @if(Session::get('cuti_tahun') == $tahun->tahun4) selected @endif>{{ $tahun->tahun4 }}</option>
                                <option value="{{ $tahun->tahun5 }}" @if(Session::get('cuti_tahun') == $tahun->tahun5) selected @endif>{{ $tahun->tahun5 }}</option>
                            @else
                                <option value="{{ $tahun->tahun1 }}" @if($tahun->tahun1 == date('Y')) selected @endif>{{ $tahun->tahun1 }}</option>
                                <option value="{{ $tahun->tahun2 }}" @if($tahun->tahun2 == date('Y')) selected @endif>{{ $tahun->tahun2 }}</option>
                                <option value="{{ $tahun->tahun3 }}" @if($tahun->tahun3 == date('Y')) selected @endif>{{ $tahun->tahun3 }}</option>
                                <option value="{{ $tahun->tahun4 }}" @if($tahun->tahun4 == date('Y')) selected @endif>{{ $tahun->tahun4 }}</option>
                                <option value="{{ $tahun->tahun5 }}" @if($tahun->tahun5 == date('Y')) selected @endif>{{ $tahun->tahun5 }}</option>
                            @endif
                        </select>
                    </td>
                    <td width="100px" style="padding-left:10px">
                        <button class="btn btn-primary" type="submit">{{ trans('all.tampilkan') }}</button><p></p>
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
                            @if(Session::has('cuti_atribut'))
                                {{ $checked = false }}
                                @for($i=0;$i<count(Session::get('cuti_atribut'));$i++)
                                    @if($atributnilai->id == Session::get('cuti_atribut')[$i])
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