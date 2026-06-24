<h2>New Contact Message</h2>
<p><strong>Name:</strong> {{ $name }}</p>
<p><strong>Email:</strong> {{ $email }}</p>
<hr>
<p><strong>Message:</strong></p>
<p>{{ nl2br(e($contactMessage)) }}</p>
