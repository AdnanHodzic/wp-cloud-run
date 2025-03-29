<?php
/**
 * @var $args array
 */

$settings = $args['settings'] ?? new stdClass();
?>
<table>
	<tr>
		<th>#</th>
		<th>Page, Post, Custom Post URL</th>
		<th>Image URLs</th>
		<?php if ( ! ( $settings->hide_image_previews ?? false ) ) : ?>
			<th>Images</th>
		<?php endif; ?>
	</tr>
	<xsl:for-each select="./sitemap:urlset/sitemap:url">
		<tr>
			<xsl:if test="position() mod 2 != 1">
				<xsl:attribute name="class">high</xsl:attribute>
			</xsl:if>
			<td>
				<xsl:value-of select="position()"/>
			</td>
			<td>
				<xsl:variable name="url">
					<xsl:value-of select="sitemap:loc"/>
				</xsl:variable>
				<a href="{$url}" target="_blank">
					<xsl:value-of select="sitemap:loc"/>
				</a>
			</td>
			<td>
				<xsl:for-each select="image:image">
					<xsl:variable name='imageURL'>
						<xsl:value-of select='image:loc'/>
					</xsl:variable>
					<div>
						<a href='{$imageURL}'>
							<xsl:value-of select='image:loc'/>
						</a>
					</div>
				</xsl:for-each>
			</td>
			<?php if ( ! ( $settings->hide_image_previews ?? false ) ) : ?>
			<td>
				<xsl:for-each select="image:image">
					<xsl:variable name='imageURL'>
						<xsl:value-of select='image:loc'/>
					</xsl:variable>
					<div class="image">
						<a href='{$imageURL}' class="thumbnail">
							<img src='{$imageURL}'/>
						</a>
					</div>
				</xsl:for-each>
			</td>
			<?php endif; ?>
		</tr>
	</xsl:for-each>
</table>