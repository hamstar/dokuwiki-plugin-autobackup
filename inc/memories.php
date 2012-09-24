
<p>These are the memories (backups) known to your Braincase.  You can revert to a prior memory by selecting it and clicking Remember</p>

<table>
	<thead>
		<tr>
			<th>Date</th>
			<th>Source</th>
			<th style="text-align: center;"><button id="apply-backup">Remember</button></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ( $backups as $b ) : ?>
		<tr>
			<td><?php echo $b->date; ?></td>
			<td><?php echo $b->source; ?></td>
			<td style="text-align: center;"><input type="radio" name="backup-selection"/></td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>