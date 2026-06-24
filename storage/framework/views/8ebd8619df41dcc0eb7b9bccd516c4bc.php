<h2>New Contact Message</h2>
<p><strong>Name:</strong> <?php echo e($name); ?></p>
<p><strong>Email:</strong> <?php echo e($email); ?></p>
<hr>
<p><strong>Message:</strong></p>
<p><?php echo e(nl2br(e($contactMessage))); ?></p>
<?php /**PATH C:\wamp64\www\exam_webshop\resources\views/mail/contact-message.blade.php ENDPATH**/ ?>