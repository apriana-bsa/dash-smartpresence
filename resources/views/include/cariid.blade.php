<script>
    {{--$(function() {--}}
        {{--$('#simpan').click(function(){--}}
            {{--var idlogabsen = $('#idlogabsen').val();--}}
            {{--var flag = $('#flag').val();--}}
            {{--var flagketerangan = $('#flagketerangan').val();--}}

            {{--$.ajax({--}}
                {{--type: "GET",--}}
                {{--url: '{{ url('generatecsrftoken') }}',--}}
                {{--data: '',--}}
                {{--cache: false,--}}
                {{--success: function (token) {--}}
                    {{--var dataString = 'idlogabsen=' + idlogabsen + '&flag=' + flag + '&flagketerangan=' + flagketerangan + '&_token=' + token;--}}
                    {{--$.ajax({--}}
                        {{--type: "POST",--}}
                        {{--url: '{{ url('flaglogabsen/submit') }}',--}}
                        {{--data: dataString,--}}
                        {{--cache: false,--}}
                        {{--success: function (html) {--}}
                            {{--//console.log(html['status']);--}}
                            {{--if (html['status'] == 'ok') {--}}
                                {{--alertSuccess(html['msg']);--}}
                                {{--//$('#tutupmodal').trigger('click');--}}
                            {{--} else {--}}
                                {{--alertError(html['msg']);--}}
                            {{--}--}}
                        {{--}--}}
                    {{--});--}}
                {{--}--}}
            {{--});--}}
        {{--});--}}
    {{--});--}}

    function pilihData(id){
        if(!isNumeric(id)){
            id = '"'+id+'"';
        }
        var jenisfield = $('#jenisfield').attr('jenis');
        give(id,"query_"+jenisfield+"_if");
        $('#tutupmodal').trigger('click');
    }

    function pilihJenisData(){
        var jenis = $('#jenis').val();
        $('#theadjenis').html('');
        $('#tbodyjenis').html('');
        $('#contentjenis').css('display', 'none');
        if(jenis != '') {

            if ($.fn.DataTable.isDataTable('.datatable')) {
                var datatable = $('.datatable').DataTable();
                datatable.destroy();
            }

            $.ajax({
                type: "GET",
                url: '{{ url('cariid') }}/data/' + jenis,
                data: '',
                cache: false,
                success: function (response) {
                    var headtable = '';
                    var bodytable = '';
                    if (jenis == 'pegawai') {
                        headtable = '<th class="nama">{{ trans('all.nama') }}</th>' +
                            '<th class="pin">{{ trans('all.pin') }}</th>' +
                            '<th class="opsi3">{{ trans('all.nomorhp') }}</th>' +
                            '<th class="opsi1">{{ trans('all.pilih') }}</th>';
                        jQuery.each(response, function (i, val) {
                            bodytable += '<tr>' +
                                '<td>' + val['nama'] + '</td>' +
                                '<td>' + val['pin'] + '</td>' +
                                '<td>' + val['nomorhp'] + '</td>' +
                                '<td><a href="#" onclick="return pilihData(' + val['id'] + ')">{{ trans('all.pilih') }}</a></td>' +
                                '</tr>';
                        });
                    } else if (jenis == 'atributnilai') {
                        headtable = '<th class="nama">{{ trans('all.atribut') }}</th>' +
                            '<th class="pin">{{ trans('all.nilai') }}</th>' +
                            '<th class="opsi1">{{ trans('all.pilih') }}</th>';
                        jQuery.each(response, function (i, val) {
                            bodytable += '<tr>' +
                                '<td>' + val['atribut'] + '</td>' +
                                '<td>' + val['nilai'] + '</td>' +
                                '<td><a href="#" onclick="return pilihData(' + val['id'] + ')">{{ trans('all.pilih') }}</a></td>' +
                                '</tr>';
                        });
                    } else if (jenis == 'agama') {
                        headtable = '<th class="opsi1">{{ trans('all.urutan') }}</th>' +
                            '<th class="nama">{{ trans('all.agama') }}</th>' +
                            '<th class="opsi1">{{ trans('all.pilih') }}</th>';
                        jQuery.each(response, function (i, val) {
                            bodytable += '<tr>' +
                                '<td>' + val['urutan'] + '</td>' +
                                '<td>' + val['agama'] + '</td>' +
                                '<td><a href="#" onclick="return pilihData(' + val['id'] + ')">{{ trans('all.pilih') }}</a></td>' +
                                '</tr>';
                        });
                    } else if (jenis == 'jamkerja') {
                        headtable = '<th class="nama">{{ trans('all.nama') }}</th>' +
                            '<th class="nama">{{ trans('all.kategori') }}</th>' +
                            '<th class="opsi5">{{ trans('all.jenis') }}</th>' +
                            '<th class="opsi1">{{ trans('all.pilih') }}</th>';
                        jQuery.each(response, function (i, val) {
                            bodytable += '<tr>' +
                                '<td>' + val['nama'] + '</td>' +
                                '<td>' + val['kategori'] + '</td>' +
                                '<td>' + val['jenis'] + '</td>' +
                                '<td><a href="#" onclick="return pilihData(' + val['id'] + ')">{{ trans('all.pilih') }}</a></td>' +
                                '</tr>';
                        });
                    } else if (jenis == 'lokasi') {
                        headtable = '<th class="nama">{{ trans('all.nama') }}</th>' +
                            '<th class="opsi5">{{ trans('all.lat') }}</th>' +
                            '<th class="opsi5">{{ trans('all.lon') }}</th>' +
                            '<th class="opsi1">{{ trans('all.pilih') }}</th>';
                        jQuery.each(response, function (i, val) {
                            bodytable += '<tr>' +
                                '<td>' + val['nama'] + '</td>' +
                                '<td>' + val['lat'] + '</td>' +
                                '<td>' + val['lon'] + '</td>' +
                                '<td><a href="#" onclick="return pilihData(' + val['id'] + ')">{{ trans('all.pilih') }}</a></td>' +
                                '</tr>';
                        });
                    } else if (jenis == 'jamkerjashift') {
                        headtable = '<th class="opsi5">{{ trans('all.urutan') }}</th>' +
                            '<th class="nama">{{ trans('all.jamkerja') }}</th>' +
                            '<th class="opsi5">{{ trans('all.namashift') }}</th>' +
                            '<th class="opsi5">{{ trans('all.kode') }}</th>' +
                            '<th class="opsi5">{{ trans('all.jenis') }}</th>' +
                            '<th class="opsi1">{{ trans('all.pilih') }}</th>';
                        jQuery.each(response, function (i, val) {
                            bodytable += '<tr>' +
                                '<td>' + val['urutan'] + '</td>' +
                                '<td>' + val['jamkerja'] + '</td>' +
                                '<td>' + val['namashift'] + '</td>' +
                                '<td>' + val['kode'] + '</td>' +
                                '<td>' + val['jenis'] + '</td>' +
                                '<td><a href="#" onclick="return pilihData(' + val['id'] + ')">{{ trans('all.pilih') }}</a></td>' +
                                '</tr>';
                        });
                    } else if (jenis == 'jamkerjashift_jenis') {
                        headtable = '<th class="nama">{{ trans('all.nama') }}</th>' +
                            '<th class="opsi5">{{ trans('all.digunakan') }}</th>' +
                            '<th class="opsi1">{{ trans('all.pilih') }}</th>';
                        jQuery.each(response, function (i, val) {
                            bodytable += '<tr>' +
                                '<td>' + val['nama'] + '</td>' +
                                '<td>' + val['digunakan'] + '</td>' +
                                '<td><a href="#" onclick="return pilihData(' + val['id'] + ')">{{ trans('all.pilih') }}</a></td>' +
                                '</tr>';
                        });
                    } else if (jenis == 'jamkerjakategori') {
                        headtable = '<th class="nama">{{ trans('all.nama') }}</th>' +
                            '<th class="opsi5">{{ trans('all.digunakan') }}</th>' +
                            '<th class="opsi1">{{ trans('all.pilih') }}</th>';
                        jQuery.each(response, function (i, val) {
                            bodytable += '<tr>' +
                                '<td>' + val['nama'] + '</td>' +
                                '<td>' + val['digunakan'] + '</td>' +
                                '<td><a href="#" onclick="return pilihData(' + val['id'] + ')">{{ trans('all.pilih') }}</a></td>' +
                                '</tr>';
                        });
                    } else if (jenis == 'alasantidakmasuk') {
                        headtable = '<th class="opsi1">{{ trans('all.urutan') }}</th>' +
                            '<th class="nama">{{ trans('all.alasan') }}</th>' +
                            '<th class="nama">{{ trans('all.kategori') }}</th>' +
                            '<th class="opsi5">{{ trans('all.digunakan') }}</th>' +
                            '<th class="opsi1">{{ trans('all.pilih') }}</th>';
                        jQuery.each(response, function (i, val) {
                            bodytable += '<tr>' +
                                '<td>' + val['urutan'] + '</td>' +
                                '<td>' + val['kategori'] + '</td>' +
                                '<td>' + val['alasan'] + '</td>' +
                                '<td>' + val['digunakan'] + '</td>' +
                                '<td><a href="#" onclick="return pilihData(' + val['id'] + ')">{{ trans('all.pilih') }}</a></td>' +
                                '</tr>';
                        });
                    } else if (jenis == 'alasantidakmasuk_kategori') {
                        headtable = '<th class="opsi1">{{ trans('all.kategori') }}</th>' +
                                    '<th class="opsi1">{{ trans('all.pilih') }}</th>';
                        bodytable = '<tr>' +
                                        '<td>{{ trans('all.sakit') }}</td>' +
                                        '<td><a href="#" onclick="return pilihData(\'s\')">{{ trans('all.pilih') }}</a></td>' +
                                      '</tr>' +
                                    '<tr>' +
                                        '<td>{{ trans('all.ijin') }}</td>' +
                                        '<td><a href="#" onclick="return pilihData(\'i\')">{{ trans('all.pilih') }}</a></td>' +
                                    '</tr>' +
                                    '<tr>' +
                                        '<td>{{ trans('all.dispensasi') }}</td>' +
                                        '<td><a href="#" onclick="return pilihData(\'d\')">{{ trans('all.pilih') }}</a></td>' +
                                    '</tr>' +
                                    '<tr>' +
                                        '<td>{{ trans('all.alpha') }}</td>' +
                                        '<td><a href="#" onclick="return pilihData(\'a\')">{{ trans('all.pilih') }}</a></td>' +
                                    '</tr>' +
                                    '<tr>' +
                                        '<td>{{ trans('all.cuti') }}</td>' +
                                        '<td><a href="#" onclick="return pilihData(\'c\')">{{ trans('all.pilih') }}</a></td>' +
                                    '</tr>';
                    }
                    $('#theadjenis').html(headtable);
                    $('#tbodyjenis').html(bodytable);
                    $('.datatable').DataTable({
                        scrollX: true,
                        order: [[0, 'asc']],
//                      columnDefs: [ {
//                        targets: 3,
//                        orderable: false
//                    } ]
                    });
                    $('.datatable').resize();
                    $('#contentjenis').css('display', '');
                }
            });
        }
    }

//    $(function() {
//        $('.datatable').DataTable({
//            scrollX: true,
//            order: [[1, 'asc']]
//        });
//    });
</script>
<div class="modal-content">
    <div class="modal-header">
        <button type="button" class="close" id='closemodal' data-dismiss="modal">&times;</button>
        <h4 class="modal-title">{{ trans('all.cari_id') }}</h4>
    </div>
    <div class="modal-body body-modal row" id="bodymodaljamkerja">
        <table width="100%">
            <tr>
                <td>{{ trans('all.jenis') }}</td>
                <td style="float:left">
                    <select class="form-control" id="jenis" onchange="return pilihJenisData()">
                        <option value=""></option>
                        <option value="pegawai">{{ trans('all.pegawai') }}</option>
                        <option value="atributnilai">{{ trans('all.atributnilai') }}</option>
                        <option value="agama">{{ trans('all.agama') }}</option>
                        <option value="jamkerja">{{ trans('all.jamkerja') }}</option>
                        <option value="lokasi">{{ trans('all.lokasi') }}</option>
                        <option value="jamkerjashift">{{ trans('all.jamkerjashift') }}</option>
                        <option value="jamkerjashift_jenis">{{ trans('all.jamkerjashift_jenis') }}</option>
                        <option value="jamkerjakategori">{{ trans('all.jamkerjakategori') }}</option>
                        @if($jenis == 'kehadiran')
                            <option value="alasantidakmasuk">{{ trans('all.alasantidakmasuk') }}</option>
                            <option value="alasantidakmasuk_kategori">{{ trans('all.alasantidakmasuk_kategori') }}</option>
                        @endif
                    </select>
                </td>
            </tr>
        </table>
        <span id="jenisfield" jenis="{{ $jenis }}"></span>
        <div id="contentjenis" style="margin-top:10px;display:none">
            <table id="tabelcontentjenis" class="datatable table table-striped table-condensed table-hover">
                <thead>
                    <tr id="theadjenis"></tr>
                </thead>
                <tbody id="tbodyjenis"></tbody>
            </table>
        </div>
    </div>
    <div class="modal-footer">
        <table width="100%">
            <tr>
                <td align="right" style="padding:0">
                    <button class="btn btn-primary" id="tutupmodal" data-dismiss="modal"><i class="fa fa-undo"></i>&nbsp;&nbsp;{{ trans('all.tutup') }}</button>
                </td>
            </tr>
        </table>
    </div>
</div>