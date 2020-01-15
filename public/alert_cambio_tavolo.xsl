<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<xsl:output method="xml" version="1.0" encoding="utf-8"/>

<xsl:include href="./report_formats.xsl"/>
<xsl:include href="./report_text_templates.xsl"/>
<xsl:include href="./report_xml_templates.xsl"/>

<xsl:template name="report_heading" match="/table/record">
<!-- HEADING AND TITLE -->
  <xsl:element name="open_print"/>
    <xsl:call-template name="PrintHeadingText">
      <xsl:with-param name="value" select="concat('Tavolo: ', /table/record/TEXT_FIELD_4)"/>
      <xsl:with-param name="font" select="'double-size'"/>
      <xsl:with-param name="length" select="20"/>
    </xsl:call-template>
</xsl:template>

<xsl:template match="/">
<!-- DATA -->
  <xsl:element name="root">
  <xsl:call-template name="report_heading"/>

  <empty_row/>
  <empty_row/>

  <!-- Cycle for following courses -->
  <xsl:element name="close_print"/>
  </xsl:element>
</xsl:template>  
</xsl:stylesheet>
