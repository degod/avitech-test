@foreach($emails as $email)
  @if($email['head'])
    <hr style="margin:0px"><h2 style="background-color: #fff;padding:15px 5px 10px;">Laravel Developer Technical Test<span style="color:gray;font-size:14px;"><br>25 messages</span></h2>
  @endif
  
    <hr style="margin:0px">
    <p style="padding:10px 5px; font-family: Arial;background-color:#fff;">
        <span style="float:right;">{{ $email['timestamp'] }}</span><br>
        <b>{{ $email['from_name'] }}</b> &lt;{{ $email['from_email'] }}&gt;<br>
        To: {{ $email['to_name'] }} &lt;{{ $email['to_email'] }}&gt;
    </p>
    <div style="border:1px solid #ccc; padding:15px; margin-bottom:30px;background-color:#fff;">
        {!! $email['body'] !!}
    </div>
@endforeach