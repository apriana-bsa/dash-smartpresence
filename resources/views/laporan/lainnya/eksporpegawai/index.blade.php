@extends('layouts.master')
@section('title', trans('all.lainnya'))
@section('content')
  
  <style type="text/css">
  td{
    padding:5px;
  }
  </style>
  <div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
      <h2>{{ trans('all.lainnya') }}</h2>
      <ol class="breadcrumb">
        <li>{{ trans('all.laporan') }}</li>
        <li>{{ trans('all.lainnya') }}</li>
        <li class="active"><strong>{{ trans('all.pegawai') }}</strong></li>
      </ol>
    </div>
    <div class="col-lg-2">

    </div>
  </div>

  <div class="wrapper wrapper-content animated fadeIn">
    <div class="row">
      <div class="col-lg-12">
          <ul class="nav nav-tabs">
            <li><a href="{{ url('laporan/lainnya/harilibur') }}">{{ trans('all.harilibur') }}</a></li>
            <li><a href="{{ url('laporan/lainnya/terlambat') }}">{{ trans('all.terlambat') }}</a></li>
            <li><a href="{{ url('laporan/lainnya/pulangawal') }}">{{ trans('all.pulangawal') }}</a></li>
            <li><a href="{{ url('laporan/lainnya/belumabsenmasuk') }}">{{ trans('all.belumabsenmasuk') }}</a></li>
            <li><a href="{{ url('laporan/lainnya/belumabsenpulang') }}">{{ trans('all.belumabsenpulang') }}</a></li>
            <li><a href="{{ url('laporan/lainnya/masuktanpajadwal') }}">{{ trans('all.masuktanpajadwal') }}</a></li>
            <li><a href="{{ url('laporan/lainnya/prosentaseabsen') }}">{{ trans('all.prosentaseabsen') }}</a></li>
            <li class="active"><a href="{{ url('laporan/lainnya/pegawai') }}">{{ trans('all.pegawai') }}</a></li>
          </ul>
          <p></p>
          <form method="post" action="">
              {{ csrf_field() }}
              <div style="margin-bottom: 10px;">
                <button class="btn btn-primary" type="submit" onclick="ke('{{ url('laporan/lainnya/pegawai/excel') }}')"><i class="fa fa-file-excel-o"></i>&nbsp;&nbsp;{{ trans('all.ekspor') }}</button>
              </div>
              @if(count($dataatributvariable) > 0)
                  <div style="padding-left: 0" class="col-lg-6">
                      <b>{{ trans('all.atributvariable') }}</b>
                      @foreach($dataatributvariable as $atributvariable)
                          <div class="col-md-12" style="margin-top:5px">
                              <input type="checkbox" name="atributvariable[]" value="{{ $atributvariable->id }}" id="semuaatributvariable_{{ $atributvariable->id }}" onclick="checkboxallclick('semuaatributvariable_{{ $atributvariable->id }}','attr_{{ $atributvariable->id }}')">&nbsp;&nbsp;
                              <span class="spancheckbox" onclick="spanallclick('semuaatributvariable_{{ $atributvariable->id }}','attr_{{ $atributvariable->id }}')">{{ $atributvariable->atribut }}</span>
                          </div>
                      @endforeach
                  </div>
              @endif
              @if(count($dataatribut) > 0)
                  <div style="padding-left: 0" class="col-lg-6">
                      <b>{{ trans('all.atribut') }}</b>
                      @foreach($dataatribut as $atribut)
                          <div class="col-md-12" style="margin-top:5px">
                              <input type="checkbox" name="atribut[]" value="{{ $atribut->id }}" id="semuaatribut_{{ $atribut->id }}" onclick="checkboxallclick('semuaatribut_{{ $atribut->id }}','attr_{{ $atribut->id }}')">&nbsp;&nbsp;
                              <span class="spancheckbox" onclick="spanallclick('semuaatribut_{{ $atribut->id }}','attr_{{ $atribut->id }}')">{{ $atribut->atribut }}</span>
                          </div>
                      @endforeach
                  </div>
              @endif
          </form>
      </div>
    </div>
  </div>
@stop