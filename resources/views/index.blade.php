@extends('layouts.app')
@section('content')
<div class="container py-4">
  <h2 class="mb-4">Hotel Room Reservation</h2>

  @if(session('info'))
    <div class="alert alert-info">{{ session('info') }}</div>
  @endif

  @error('rooms')
    <div class="alert alert-danger">{{ $message }}</div>
  @enderror

  @if($userRooms->count())
  <div class="alert alert-info">
    <strong>Your Booked Rooms:</strong> {{ $userRooms->implode(', ') }}
  </div>
  @endif

  <form id="bookingForm" method="POST" action="book" class="mb-4">
    @csrf
    <div class="form-group row align-items-center">
      <label class="col-sm-3 col-form-label">Number of Rooms (max 5):</label>
      <div class="col-sm-2">
        <input type="number" name="rooms" min="1" max="5" value="1" class="form-control">
      </div>
      <div class="col-sm-7">
        <button type="submit" class="btn btn-primary">Book</button>
        <button id="randBtn" class="btn btn-secondary ml-2">Random Occupancy</button>
        <button id="resetBtn" class="btn btn-danger ml-2">Reset my booking</button>
        <button id="resetAllBtn" class="btn btn-danger ml-2">Reset entire booking</button>
      </div>
    </div>
  </form>

  <div id="roomGrid" class="d-flex flex-column-reverse border p-3 bg-light rounded"></div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function(){
  const renderGrid = (rooms) => {
    const floors = {};
    rooms.forEach(r => {
      if (!floors[r.floor]) floors[r.floor] = [];
      floors[r.floor].push(r);
    });

    $('#roomGrid').empty();
    for (let f = 10; f >= 1; f--) {
      const row = $('<div class="d-flex align-items-center mb-2"></div>');

      row.append(`<div class="mr-2 font-weight-bold" style="width:40px">F${f}</div>`);

      const sorted = (floors[f] || []).sort((a,b)=> a.room_number.localeCompare(b.room_number));
      sorted.forEach(r => {
        const box = $('<div class="border text-center mr-1 rounded" style="width:40px;height:40px;line-height:40px;font-size:12px;"></div>')
          .text(r.room_number)
          .attr('title', r.room_number + (r.is_booked ? ' (Booked)' : ' (Available)'))
          .addClass(r.is_booked ? 'bg-danger text-white' : 'bg-success text-white');
        row.append(box);
      });
      $('#roomGrid').append(row);
    }
  };

  const fetchRooms = () => {
    $.getJSON('api/rooms', renderGrid);
  };

  fetchRooms();

  $('#randBtn').click(function(e){
    e.preventDefault();
    $.post('generate-random', {_token:'{{ csrf_token() }}'}, function() {
        location.reload(); 
    });
  });
    $('#resetBtn').click(function(e){
        e.preventDefault();
        $.post('reset', {_token:'{{ csrf_token() }}'}, function() {
            location.reload(); 
        });
    });
    $('#resetAllBtn').click(function(e){
        e.preventDefault();
        $.post('reset-all', {_token:'{{ csrf_token() }}'}, function() {
            location.reload(); 
        });
    });
    
  $('#bookingForm').submit(function(){
    setTimeout(fetchRooms, 500);
  });
});
</script>
@endsection