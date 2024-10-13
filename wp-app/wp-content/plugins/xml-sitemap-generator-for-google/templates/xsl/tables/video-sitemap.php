<table>
	<tr>
		<th>#</th>
		<th>Page, Post, Custom Post URL</th>
		<th>Thumbnail</th>
		<th>Video</th>
		<th>Description</th>
		<th>Duration</th>
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
				<xsl:for-each select="video:video">
					<xsl:variable name='thumbURL'>
						<xsl:value-of select='video:thumbnail_loc'/>
					</xsl:variable>
					<div class="loc-item">
						<a href='{$thumbURL}' class="thumbnail">
							<img src='{$thumbURL}'/>
						</a>
					</div>
				</xsl:for-each>
			</td>
			<td>
				<xsl:for-each select="video:video">
					<xsl:variable name='videoURL'>
						<xsl:value-of select='video:player_loc'/>
					</xsl:variable>
					<div class="loc-item">
						<a href='{$videoURL}'>
							<xsl:value-of select='video:title'/>
						</a>
					</div>
				</xsl:for-each>
			</td>
			<td>
				<xsl:for-each select="video:video">
					<div class="loc-item">
						<xsl:value-of select='video:description'/>
					</div>
				</xsl:for-each>
			</td>
			<td>
				<xsl:for-each select="video:video">
					<div class="loc-item">
						<xsl:value-of select='video:duration'/>
					</div>
				</xsl:for-each>
			</td>
		</tr>
	</xsl:for-each>
</table>
