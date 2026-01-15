<?xml version="1.0" encoding="ISO-8859-1"?>
			<xsl:stylesheet version="1.0" xmlns:html="http://www.w3.org/TR/REC-html40" xmlns:sitemap="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
			<xsl:template match="/">
			  <html xmlns="http://www.w3.org/1999/xhtml">
			  <head>
			      <title>XML Sitemap Feed</title>
			      <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
			      <style type="text/css">body{font-family:Arial;font-size:12pt;}table thead tr th{background-color:#eee;}td{font-size:12px;padding: 6px 12px;text-align:left;}th{font-size:12px;padding: 6px 12px;text-align:center;}a{color:#2f2f2f}tr:nth-child(2n){background-color:#eee;}</style>
			    </head>
			  <body>
			  <h2>XML Sitemap Feed</h2>
			    <table cellpadding="5">
			      <thead>
			        <tr>
			          <th>#</th>
			          <th>Sitemap</th>
			          <th>Modified</th>
			        </tr>
			      </thead>
			      <tbody>
			      <xsl:for-each select="sitemap:sitemapindex/sitemap:sitemap">
			      <tr>
			        <td><xsl:value-of select="position()"/></td>
			        <td><xsl:variable name="loc" select="sitemap:loc"/><a href="{$loc}" target="_blank"><xsl:value-of select="sitemap:loc"/></a></td>
			        <td><xsl:value-of select="sitemap:lastmod"/></td>
			      </tr>
			      </xsl:for-each>
			      </tbody>
			    </table>
			  </body>
			  </html>
			</xsl:template>
			</xsl:stylesheet>