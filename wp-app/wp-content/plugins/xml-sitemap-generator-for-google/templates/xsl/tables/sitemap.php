<table>
	<tr>
		<th>Page URL</th>
		<th>Priority</th>
		<th>Frequency</th>
		<th>Last Modified</th>
	</tr>
	<xsl:variable name="lower" select="'abcdefghijklmnopqrstuvwxyz'"/>
	<xsl:variable name="upper" select="'ABCDEFGHIJKLMNOPQRSTUVWXYZ'"/>
	<xsl:for-each select="./sitemap:urlset/sitemap:url">
		<tr>
			<xsl:if test="position() mod 2 != 1">
				<xsl:attribute name="class">high</xsl:attribute>
			</xsl:if>
			<td>
				<xsl:variable name="page">
					<xsl:value-of select="sitemap:loc"/>
				</xsl:variable>
				<a target="_blank" href="{$page}">
					<xsl:value-of select="sitemap:loc"/>
				</a>
			</td>
			<td>
				<xsl:value-of select="sitemap:priority"/>
			</td>
			<td>
				<xsl:value-of select="sitemap:changefreq"/>
			</td>
			<td>
				<xsl:value-of select="concat(substring(sitemap:lastmod, 0, 11), concat(' ', substring(sitemap:lastmod, 12, 5)))"/>
			</td>
		</tr>
	</xsl:for-each>
</table>