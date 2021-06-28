@if(isset($pegawai))
    <script>
    $(function(){
        setTimeout(function(){
            $('#tablaporan').addClass('active');
        },100);
    });
    </script>
    <div class="ibox-content">
        {{ trans('all.detailpegawai') }}
        <p></p>
        <table>
            <tr>
                <td>
                    <a href="{{ url('fotonormal/pegawai/'.$pegawai->id) }}" title="{{ $pegawai->nama }}" data-gallery="">
                        <img id="imgInp" width=120 style='border-radius: 50%;' height=120 src="{{ url('foto/pegawai/'.$pegawai->id) }}">
                    </a>
                </td>
                <td>
                    <table>
                        <tr>
                            <td width="110px">{{ trans('all.nama') }}</td>
                            <td>: {{ $pegawai->nama }}</td>
                        </tr>
                        <tr>
                            <td>{{ trans('all.pin') }}</td>
                            <td>: {{ $pegawai->pin }}</td>
                        </tr>
                        <tr>
                            <td>{{ trans('all.nomorhp') }}</td>
                            <td>: {{ $pegawai->nomorhp }}</td>
                        </tr>
                        @if(count($atributpegawai) > 0)
                            @foreach($atributpegawai as $key)
                                <tr>
                                    <td>{{ $key->atribut }}</td>
                                    <td>: {{ $key->nilai }}</td>
                                </tr>
                            @endforeach
                        @endif
                        @if($lokasipegawai != '')
                            <tr>
                                <td>{{ trans('all.lokasi') }}</td>
                                <td>: {{ $lokasipegawai->lokasi }}</td>
                            </tr>
                        @endif
                    </table>
                </td>
            </tr>
        </table>
        <hr style="margin-bottom:5px">
        <ul class="nav nav-tabs" style="padding:10px;padding-bottom:0">
            <li class="active"><a data-toggle="tab" href="#tab-1">{{ trans('all.rekapitulasi') }}</a></li>
            <li><a data-toggle="tab" onclick="resizeDatatable('riwayat')" href="#tab-2">{{ trans('all.riwayatkehadiran') }}</a></li>
            <li><a data-toggle="tab" onclick="resizeDatatable('ijintidakmasuk')" href="#tab-3">{{ trans('all.ijintidakmasuk') }}</a></li>
            @if($adajamkerjashift == 'ada')
                <li><a data-toggle="tab" onclick="resizeDatatable('jadwalshift')" href="#tab-4">{{ trans('all.jadwalshift') }}</a></li>
            @endif
        </ul>
        <p></p>
        <div class="tab-content">
            <div id="tab-1" class="tab-pane active">
                <div class="full-height-scroll">
                    <div class="full-height-scroll">
                        <table width=100% class="table table-responsive datatablerekapitulasi table-striped table-condensed table-hover">
                            <thead>
                            <tr>
                                <td class="opsi2"><b>{{ trans('all.tanggal') }}</b></td>
                                <td class="opsi1"><center><b>{{ trans('all.libur') }}</b></center></td>
                                <td class="opsi1"><b>{{ trans('all.harilibur') }}</b></td>
                                <td class="opsi2"><center><b>{{ trans('all.masukkerja') }}</b></center></td>
                                <td class="opsi5"><b>{{ trans('all.alasantidakmasuk') }}</b></td>
                                <td class="opsi5"><b>{{ trans('all.jamkerja') }}</b></td>
                                <td class="opsi5"><b>{{ trans('all.jadwalkerja') }}</b></td>
                                <td class="opsi3"><b>{{ trans('all.alasanmasuk') }}</b></td>
                                <td class="opsi4"><b>{{ trans('all.waktumasuk') }}</b></td>
                                <td class="opsi4"><b>{{ trans('all.waktukeluar') }}</b></td>
                                <td class="opsi1"><b>{{ trans('all.terlambat') }}</b></td>
                                <td class="opsi4"><b>{{ trans('all.pulanglebihawal') }}</b></td>
                                <td class="opsi2"><b>{{ trans('all.lamakerja') }}</b></td>
                                <td class="opsi2"><b>{{ trans('all.lamalembur') }}</b></td>
                                <td class="opsi5"><b>{{ trans('all.kelengkapanpresensi') }}</b></td>
                            </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
            <div id="tab-2" class="tab-pane">
                <div class="full-height-scroll">
                    <table width=100% class="table table-responsive datatableriwayat table-striped table-condensed table-hover">
                        <thead>
                            <tr>
                                <td class="opsi5"><b>{{ trans('all.tanggal') }}</b></td>
                                <td class="opsi2"><center><b>{{ trans('all.masukkeluar') }}</b></center></td>
                                <td class="opsi5"><b>{{ trans('all.alasan') }}</b></td>
                                <td class="opsi5"><center><b>{{ trans('all.terhitungkerja') }}</b></center></td>
                                <td class="opsi1"><center><b>{{ trans('all.status') }}</b></center></td>
                                <td class="alamat"><b>{{ trans('all.mesin') }}</b></td>
                                <td class="opsi5"><b>{{ trans('all.lat') }}</b></td>
                                <td class="opsi5"><b>{{ trans('all.lon') }}</b></td>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
            <div id="tab-3" class="tab-pane">
                <div class="full-height-scroll">
                    <table width=100% class="table table-responsive datatableijintidakmasuk table-striped table-condensed table-hover">
                        <thead>
                            <tr>
                                <td class="alamat"><b>{{ trans('all.tanggal') }}</b></td>
                                <td class="nama"><b>{{ trans('all.alasan') }}</b></td>
                                <td class="keterangan"><b>{{ trans('all.keterangan') }}</b></td>
                                <td class="opsi2"><center><b>{{ trans('all.status') }}</b></center></td>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
            <div id="tab-4" class="tab-pane">
                <div class="full-height-scroll">
                    <div class="full-height-scroll">
                        <table width=100% class="table table-responsive datatablejadwalshift table-striped table-condensed table-hover">
                            <thead>
                            <tr>
                                <td class="opsi5"><b>{{ trans('all.tanggal') }}</b></td>
                                <td class="nama"><b>{{ trans('all.jamkerja') }}</b></td>
                                <td class="nama"><b>{{ trans('all.namashift') }}</b></td>
                            </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    function resizeDatatable(jenis){
        setTimeout(function(){
            $('.datatable'+jenis).resize();
        },1);
    }

    $(function() {
        $('.datatableriwayat').DataTable({
            processing: true,
            bStateSave: true,
            serverSide: true,
            scrollX: true,
            ajax: {
                url: '{!! url("laporan/perpegawai/riwayat/".$pegawai->id) !!}',
                type: "POST",
                data: { _token: '{!! csrf_token() !!}' }
            },
            language: lang_datatable,
            columns: [
                { data: 'tanggal', name: 'tanggal',
                    render: function (data) {
                        var ukDateTime = data.split(' ');
                        var ukDate = ukDateTime[0].split('-');
                        return ukDate[2] + "/" + ukDate[1] + "/" + ukDate[0] + " " +ukDateTime[1];
                    }
                },
                { data: 'masukkeluar', name: 'masukkeluar' },
                { data: 'alasan', name: 'alasan' },
                { data: 'terhitungkerja', name: 'terhitungkerja' },
                { data: 'status', name: 'status' },
                { data: 'mesin', name: 'mesin' },
                { data: 'lat', name: 'lat' },
                { data: 'lon', name: 'lon' }
            ]
        });

        $('.datatableriwayat').resize();

        $('.datatablerekapitulasi').DataTable({
            processing: true,
            bStateSave: true,
            serverSide: true,
            scrollX: true,
            ajax: {
                url: '{!! url("laporan/perpegawai/rekapitulasi/".$pegawai->id) !!}',
                type: "POST",
                data: { _token: '{!! csrf_token() !!}' }
            },
            language: lang_datatable,
            columns: [
                { data: 'tanggal', name: 'tanggal',
                    render: function (data) {
                        var ukDate = data.split('-');
                        return ukDate[2] + "/" + ukDate[1] + "/" + ukDate[0];
                    }
                },
                { data: 'libur', name: 'libur' },
                { data: 'harilibur', name: 'harilibur' },
                { data: 'masukkerja', name: 'masukkerja' },
                { data: 'alasantidakmasuk', name: 'alasantidakmasuk' },
                { data: 'jamkerja', name: 'jamkerja' },
                { data: 'jadwalkerja', name: 'jadwalkerja' },
                { data: 'alasanmasuk', name: 'alasanmasuk' },
                { data: 'waktumasuk', name: 'waktumasuk',
                    render: function (data) {
                        if(data != null){
                            var ukDateTime = data.split(' ');
                            //var ukDate = ukDateTime[0].split('-');
                            //return ukDate[2] + "/" + ukDate[1] + "/" + ukDate[0] + " " +ukDateTime[1];
                            return ukDateTime[1];
                        }else{
                            return data;
                        }
                    }
                },
                { data: 'waktukeluar', name: 'waktukeluar',
                    render: function (data) {
                        if(data != null){
                            var ukDateTime = data.split(' ');
                            //var ukDate = ukDateTime[0].split('-');
                            //return ukDate[2] + "/" + ukDate[1] + "/" + ukDate[0] + " " +ukDateTime[1];
                            return ukDateTime[1];
                        }else{
                            return data;
                        }
                    }
                },
                { data: 'terlambat', name: 'terlambat' },
                { data: 'pulanglebihawal', name: 'pulanglebihawal' },
                { data: 'lamakerja', name: 'lamakerja' },
                { data: 'lamalembur', name: 'lamalembur' },
                { data: 'kelengkapanpresensi', name: 'kelengkapanpresensi' }
            ]
        });

        $('.datatablerekapitulasi').resize();

        $('.datatableijintidakmasuk').DataTable({
            processing: true,
            bStateSave: true,
            serverSide: true,
            scrollX: true,
            ajax: {
                url: '{!! url("laporan/perpegawai/ijintidakmasuk/".$pegawai->id) !!}',
                type: "POST",
                data: { _token: '{!! csrf_token() !!}' }
            },
            language: lang_datatable,
            columns: [
                { data: 'tanggalawal', name: 'tanggalawal' },
                { data: 'alasan', name: 'alasan' },
                { data: 'keterangan', name: 'keterangan' },
                { data: 'status', name: 'status' }
            ]/*,
            order: [[1, 'asc']]*/
        });

        $('.datatablejadwalshift').DataTable({
            processing: true,
            bStateSave: true,
            serverSide: true,
            scrollX: true,
            ajax: {
                url: '{!! url("laporan/perpegawai/jadwalshift/".$pegawai->id) !!}',
                type: "POST",
                data: { _token: '{!! csrf_token() !!}' }
            },
            language: lang_datatable,
            columns: [
                { data: 'tanggal', name: 'tanggal',
                    render: function (data) {
                        var ukDate = data.split('-');
                        return ukDate[2] + "/" + ukDate[1] + "/" + ukDate[0];
                    }
                },
                { data: 'jamkerja', name: 'jamkerja' },
                { data: 'namashift', name: 'namashift' }
            ],
            order: [[0, 'asc']]
        });
    });
    </script>
@endif