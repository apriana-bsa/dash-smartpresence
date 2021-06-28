<div class="ibox-content">
    <table>
        @for($i=0;$i<count($data);$i++)
            <tr>
                <td>{{ $data[$i]['tanggal'].', '.$data[$i]['hari'] }}</td>
                <td><i class="fa fa-arrow-right"></i></td>

                @if($data[$i]['ijintidakmasuk'] != '')
                    <td style="color:#ccc">
                        {!!  trans('all.ijintidakmasuk').'<br>'.trans('all.alasan').': '.$data[$i]['ijintidakmasuk'].'<br>'.trans('all.keterangan').': '.$data[$i]['ijintidakmasukketerangan'] !!}
                    </td>
                @elseif($data[$i]['jenis'] == '')
                    <td style="color:#ccc">{{ trans('all.tidakadajamkerja') }}</td>
                @elseif($data[$i]['jenis'] != 'shift')
                    <td>{{ trans('all.jamkerjafull').' : '.$data[$i]['nama'] }}</td>
                @else
                    @if($data[$i]['libur'] == 'y')
                        <td>
                            <input type="checkbox" class="idjadwalshift_{{ $dari }}" name="idjadwalshift_{{ $dari }}[]" value="{{ $data[$i]['idjamkerja'].'@'.$data[$i]['tanggal'].':'}}" id="jadwalshift_{{ $dari.'_'.$i.'_' }}">
                            <span style="cursor:pointer" onclick="spanclick('jadwalshift_{{ $dari.'_'.$i.'_' }}')">{{ trans('all.libur') }}</span>
                        </td>
                    @else
                        @if(count($data[$i]['jadwal']) > 0)
                            <td style="padding-left:0">
                                <table>
                                    <tr>
                                        @for($j=0;$j<count($data[$i]['jadwal']);$j++)
                                            <td style="padding:0 5px">
                                                <input type="checkbox" class="idjadwalshift_{{ $dari }}" name="idjadwalshift_{{ $dari }}[]" value="{{ $data[$i]['idjamkerja'].'@'.$data[$i]['tanggal'].':'.$data[$i]['jadwal'][$j]['idshift']}}" id="jadwalshift_{{ $dari.'_'.$i.'_'.$data[$i]['jadwal'][$j]['idshift'] }}">
                                                <span style="cursor:pointer" onclick="spanclick('jadwalshift_{{ $dari.'_'.$i.'_'.$data[$i]['jadwal'][$j]['idshift'] }}')">{{ $data[$i]['jadwal'][$j]['namashift'] }}</span>
                                            </td>
                                        @endfor
                                    </tr>
                                </table>
                            </td>
                        @else
                            <td style="color:#ccc">{{ trans('all.tidakadajadwal') }}</td>
                        @endif
                    @endif
                @endif
            </tr>
        @endfor
    </table>
</div>