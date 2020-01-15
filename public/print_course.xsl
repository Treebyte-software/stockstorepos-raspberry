<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method="xml" version="1.0" encoding="utf-8" />

<xsl:include href="./report_formats.xsl"/>
<xsl:include href="./report_text_templates.xsl"/>
<xsl:include href="./report_xml_templates.xsl"/>

<xsl:template name="report_heading" match="/table/record">
<!-- HEADING AND TITLE -->
  <xsl:element name="open_print"></xsl:element>
  <xsl:call-template name="PrintHeadingText">
    <xsl:with-param name="value" select="concat('Tavolo: ', /table/record/LUNCH_TABLE_DESC)" />
    <xsl:with-param name="font" select="'double-size'" />
    <xsl:with-param name="length" select="20" />
  </xsl:call-template>
  <xsl:element name="empty_row" />
  <xsl:call-template name="PrintHeadingText">
    <xsl:with-param name="value" select="concat('Numero: ', format-number(/table/record/TRANSACTION_NUM, '#####0', 'quantity'))" />
    <xsl:with-param name="font" select="'bold'" />
    <xsl:with-param name="length" select="40" />
  </xsl:call-template>
  <xsl:call-template name="CurrentDateTime">
    <xsl:with-param name="caption" select="'Stampato: '" />
    <xsl:with-param name="font" select="'bold'" />
    <xsl:with-param name="format" select="'dd/mm hh:nn:ss'" />
  </xsl:call-template>
  <xsl:call-template name="PrintHeadingText">
    <xsl:with-param name="value" select="concat('Operatore: ', /table/record/OPERATOR_DESC)" />
    <xsl:with-param name="font" select="'normal'" />
    <xsl:with-param name="length" select="40" />
  </xsl:call-template>
  <xsl:element name="empty_row" />
  <xsl:call-template name="PrintHeadingText">
    <xsl:with-param name="value" select="/table/record/PRODUCTION_CENTER_DESC" />
    <xsl:with-param name="font" select="'double-width'" />
    <xsl:with-param name="length" select="20" />
  </xsl:call-template>
  <xsl:element name="empty_row" />
  <xsl:if test="/table/record/COURSE_DESC != ''">
    <xsl:call-template name="PrintHeadingText">
      <xsl:with-param name="value" select="/table/record/COURSE_DESC" />
      <xsl:with-param name="font" select="'double-size'" />
      <xsl:with-param name="length" select="20" />
    </xsl:call-template>
  </xsl:if>
  <xsl:if test="/table/record/COURSE_DESC = ''">
    <xsl:call-template name="PrintHeadingText">
      <xsl:with-param name="value" select="'Prima portata'" />
      <xsl:with-param name="font" select="'double-size'" />
      <xsl:with-param name="length" select="20" />
    </xsl:call-template>
  </xsl:if>
  <xsl:element name="empty_row" />
</xsl:template>

<xsl:template name="columns_headings" match="/table/record">
  <xsl:call-template name="PrintHeadingText">
    <xsl:with-param name="value" select="'----------------------------------------'" />
    <xsl:with-param name="font" select="'normal'" />
    <xsl:with-param name="length" select="40" />
  </xsl:call-template>
</xsl:template>

<xsl:template match="/">
<!-- DATA -->
<!-- DATA -->
<xsl:element name="root">
  <xsl:call-template name="report_heading" />
<!--  <xsl:call-template name="columns_headings" />-->
  <xsl:for-each select="table/record">
    <xsl:call-template name="PrintDetailText">
      <xsl:with-param name="value" select="concat(format-number(QUANTITY, '##0', 'currency'), ' x ', substring(ARTICLE_DESC, 1, 34))" />
      <xsl:with-param name="font" select="'double-width'" />
      <xsl:with-param name="length" select="20" />
    </xsl:call-template>
    <xsl:if test="ARTICLE_NOTES != ''">
      <xsl:call-template name="PrintDetailText">
        <xsl:with-param name="value" select="concat('  ', substring(ARTICLE_NOTES, 1, 38))" />
        <xsl:with-param name="font" select="'underline'" />
        <xsl:with-param name="length" select="40" />
      </xsl:call-template>
      <xsl:element name="empty_row" />
    </xsl:if>
  </xsl:for-each>
<xsl:element name="close_print"></xsl:element>
</xsl:element>
</xsl:template>  
</xsl:stylesheet>