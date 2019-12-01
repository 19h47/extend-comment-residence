<?php

?>

<table class="form-table">
	<tr>
		<th scope="row"><label for="title"><?php esc_html_e( 'Titre' ); ?></label></th>
		<td>
			<input type="text" name="title" value="<?php echo esc_attr( $title ); ?>" class="regular-text ltr" />
		</td>
	</tr>

	<tr>
		<th scope="row"><label for="date_visit"><?php esc_html_e( 'Date de la visite' ); ?></label></th>
		<td>
			<input type="date" name="date_visit" value="<?php echo esc_attr( $date_visit ); ?>" class="regular-text ltr" />
		</td>
	</tr>

	<tr>
		<th scope="row"><label for="phone"><?php esc_html_e( 'Numéro de téléphone' ); ?></label></th>
		<td>
			<input type="tel" name="phone" value="<?php echo esc_attr( $phone ); ?>" class="regular-text ltr" />
		</td>
	</tr>

<?php foreach ( $ratings as $rating ) {  ?>
	<tr>
		<th scope="row"><label for="<?php echo $rating['id'] ?>"><?php echo $rating['label'] ?></label></th>
		<td>
			<fieldset>
				<legend class="screen-reader-text"><?php echo $rating['label'] ?></legend>
				<?php for( $i = 1; $i <= 5; $i += 1 ) {
					echo '<label>';

					echo "<input type=\"radio\" name=\"{$rating['id']}\" id=\"{$rating['id']}\" value=\"{$i}\"";
					if ( $rating['note'] === $i ) {
						echo ' checked="checked"';
					}

					echo ' />';
					echo "&nbsp;{$i}";
					echo '</label>';

					if ( 5 !== $i ) {
						echo '<br>';
					}
				}
				?>
			</fieldset>
		</td>
	</tr>
<?php } ?>
</table>
