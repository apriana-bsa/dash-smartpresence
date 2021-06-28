@extends('layouts.master')
@section('title', trans('all.'.$menu))
@section('content')

  <script>
    @if(Session::get('message'))
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
    @endif

    function submitForm(submit,kembali,jenis){
      freezeButtons(submit,kembali,jenis);
      $('#submitform').trigger('click');
    }
  </script>
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
      @if($menu == 'alasanmasukkeluar')
          <h2>{{ trans('all.menu_alasanmasukkeluar') }}</h2>
      @elseif($menu == 'alasantidakmasuk')
          <h2>{{ trans('all.menu_alasantidakmasuk') }}</h2>
      @elseif($menu == 'laporankomponenmasterurutanperhitungan')
          <h2>{{ trans('all.laporankomponenmasterperhitungan') }}</h2>
      @elseif($menu == 'laporankomponenmasterurutantampilan')
          <h2>{{ trans('all.laporankomponenmasterurutantampilan') }}</h2>
      @elseif($menu == 'alasantidakmasuk')
          <h2>{{ trans('all.menu_alasantidakmasuk') }}</h2>
      @else
        <h2>{{ trans('all.'.($menu == 'payrollkomponenmasterurutanperhitungan' || $menu == 'payrollkomponenmasterurutantampilan' ? 'payrollkomponenmaster' : $menu)) }}</h2>
      @endif
      <ol class="breadcrumb">
        {!! $breadcrumb !!}
        <li class="active"><strong>{{ trans('all.urutan') }}</strong></li>
      </ol>
    </div>
    <div class="col-lg-2"></div>
  </div>
  <div class="wrapper wrapper-content animated fadeIn">
    <div class="row">
      <div class="col-lg-12">
        <div class="ibox float-e-margins">
          <div class="ibox-content">
              {{-- <form method="post" action="" onsubmit="return freezeButton()"> --}}
              <form method="post" action="">
                  {{ csrf_field() }}
                  <input type="hidden" value="{{ $url }}" name="url">
                  {{-- ul id foo hanya pancingan supaya bisa  order by dengan benar makanya dibuat display none--}}
                  @if($data != '' && count($data) > 10)
                    <button id="submit1" type="button" onclick="return submitForm('submit1','kembali','.')" class="submit1 kembali ladda-button btn btn-primary slide-left"><span class="label2"><i class='fa fa-save'></i>&nbsp;&nbsp;{{ trans('all.simpan') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
                    <button id="kembali1" type="button" onclick="ke('{{ $url }}')" class="kembali btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.batal') }}</button>
                  @endif
                  <ul style="display:none" id="foo" class="block__list block__list_words"></ul>
                  <ul id="bar" class="block__list block__list_words">
                      @if($data != '')
                          @foreach($data as $key)
                            <li style="cursor:-webkit-grabbing"><span class="drag-handle">&#9776;</span>&nbsp;<input type="hidden" value="{{ $key->id }}" name="idurutan[]">{{ $key->nama }}</li>
                          @endforeach
                      @endif
                  </ul>
                  <button id="submit" type="button" onclick="return submitForm('submit','kembali','.')" class="submit kembali ladda-button btn btn-primary slide-left"><span class="label2"><i class='fa fa-save'></i>&nbsp;&nbsp;{{ trans('all.simpan') }}</span> <span class="spinner"></span></button>&nbsp;&nbsp;
                  <button id="kembali" type="button" onclick="ke('{{ $url }}')" class="kembali btn btn-primary"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.batal') }}</button>
                  <button type="submit" id="submitform" style="display:none"></button>
                  <br>
                  <p></p>
                  *{!!  trans('all.keteranganurutan') !!}
              </form>
          </div>
        </div>
      </div>
    </div>
  </div>
  <link href="{{ asset('lib/css/appSortable.css') }}" rel="stylesheet" type="text/css" />
  <script src="{{ asset('lib/js/Sortable.js') }}"></script>
  <script src="{{ asset('lib/js/appSortable.js') }}"></script>
@stop