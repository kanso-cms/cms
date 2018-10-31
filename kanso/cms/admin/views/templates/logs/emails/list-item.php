<th><?php echo $email['to_email']; ?></th>
<th><?php echo $email['subject']; ?></th>
<th><?php echo $email['from_name']; ?></th>
<th><?php echo $email['from_email']; ?></th>
<th><?php echo date('l jS F Y - h:i:s A', $email['date']); ?></th>
<th><?php echo $email['format']; ?></th>
<th>
	<button type="button" class="btn btn-pure btn-xs tooltipped tooltipped-n" onclick="window.open('/admin/email-preview/<?php echo $email['id']; ?>/', '_blank', 'fullscreen=no,height=800,width=650')" data-tooltip="Preview email" style="margin-top: 6px;">
		<span class="glyph-icon glyph-icon-eye icon-md"></span>
	</button>
</th>
<th>
	<button type="button" class="btn btn-pure btn-xs btn-primary tooltipped tooltipped-n" onclick="document.getElementById('resend-<?php echo $i; ?>').submit()" data-tooltip="Resend email" style="margin-top: 6px;">
		<span class="glyph-icon glyph-icon-envelope icon-md"></span>
	</button>
</th>
<form method="post" id="resend-<?php echo $i; ?>" style="display: none">
	<input type="hidden" name="access_token" value="<?php echo $ACCESS_TOKEN; ?>">
	<input type="hidden" name="id" value="<?php echo $email['id']; ?>">
</form>