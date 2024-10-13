<table>
	<tr>
		<th>#</th>
		<th>Title</th>
		<th>Language</th>
		<th>Keywords</th>
		<th>Stocks</th>
		<th>Publication Date</th>
	</tr>
	<xsl:variable name="lower" select="'abcdefghijklmnopqrstuvwxyz'"/>
	<xsl:variable name="upper" select="'ABCDEFGHIJKLMNOPQRSTUVWXYZ'"/>
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
				<a href="{$url}">
					<xsl:if test="news:news">
						<xsl:value-of select="news:news/news:title"/>
					</xsl:if>
					<xsl:if test="not(news:news)">
						<xsl:value-of select="sitemap:loc"/>
					</xsl:if>
				</a>
			</td>
			<td>
				<xsl:value-of select="news:news/news:publication/news:language"/>
			</td>
			<td>
				<xsl:value-of select="news:news/news:keywords"/>
			</td>
			<td>
				<xsl:value-of select="news:news/news:stock_tickers"/>
			</td>
			<td>
				<xsl:if test="news:news">
					<xsl:value-of select="concat(substring(news:news/news:publication_date, 0, 11), concat(' ', substring(news:news/news:publication_date, 12, 5)))"/>
				</xsl:if>
				<xsl:if test="sitemap:lastmod">
					<xsl:value-of select="concat(substring(sitemap:lastmod, 0, 11), concat(' ', substring(sitemap:lastmod, 12, 5)))"/>
				</xsl:if>
			</td>
		</tr>
	</xsl:for-each>
</table>
