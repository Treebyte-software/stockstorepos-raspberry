<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">

<xsl:template name="PrintCenteredHeadingText">
  <xsl:param name="value" select="''" />
  <xsl:param name="font" select="''" />
  <xsl:param name="length" select="''" />
  
<xsl:element name="print_heading_text">
  <xsl:attribute name="font_style"><xsl:value-of select="$font"/></xsl:attribute>
  <xsl:attribute name="value">
    <xsl:call-template name="CenterString">
      <xsl:with-param name="string" select="$value" />
      <xsl:with-param name="length" select="$length" />
      <xsl:with-param name="addcrlf" select="0" />
    </xsl:call-template>
  </xsl:attribute>
</xsl:element>
</xsl:template>

<xsl:template name="PrintHeadingText">
  <xsl:param name="value" select="''" />
  <xsl:param name="font" select="''" />
  <xsl:param name="length" select="''" />
  
<xsl:element name="print_heading_text">
  <xsl:attribute name="font_style"><xsl:value-of select="$font"/></xsl:attribute>
  <xsl:attribute name="value">
    <xsl:call-template name="LeftString">
      <xsl:with-param name="string" select="$value" />
      <xsl:with-param name="length" select="$length" />
      <xsl:with-param name="addcrlf" select="0" />
    </xsl:call-template>
  </xsl:attribute>
</xsl:element>
</xsl:template>

<xsl:template name="PrintDetailText">
  <xsl:param name="value" select="''" />
  <xsl:param name="font" select="''" />
  <xsl:param name="length" select="''" />
  
<xsl:element name="print_detail_text">
  <xsl:attribute name="font_style"><xsl:value-of select="$font"/></xsl:attribute>
  <xsl:attribute name="value">
    <xsl:call-template name="LeftString">
      <xsl:with-param name="string" select="$value" />
      <xsl:with-param name="length" select="$length" />
      <xsl:with-param name="addcrlf" select="0" />
    </xsl:call-template>
  </xsl:attribute>
</xsl:element>
</xsl:template>




<xsl:template name="PrintJustifiedText">
  <xsl:param name="valueLeft" select="''" />
  <xsl:param name="valueRight" select="''" />
  <xsl:param name="font" select="''" />
  <xsl:param name="lengthLeft" select="''" />
  <xsl:param name="lengthRight" select="''" />
  
  <xsl:element name="print_detail_text">
    <xsl:attribute name="font_style"><xsl:value-of select="$font"/></xsl:attribute>
    <xsl:attribute name="value">
	  <xsl:call-template name="LeftString">
	    <xsl:with-param name="string" select="$valueLeft" />
	    <xsl:with-param name="length" select="$lengthLeft" />
	    <xsl:with-param name="addcrlf" select="0" />
	  </xsl:call-template>
	  <xsl:call-template name="RightString">
	    <xsl:with-param name="string" select="$valueRight" />
	    <xsl:with-param name="length" select="$lengthRight" />
	    <xsl:with-param name="addcrlf" select="0" />
	  </xsl:call-template>
    </xsl:attribute>
  </xsl:element>

</xsl:template>








<xsl:template name="CurrentDate">
  <xsl:param name="caption" select="''" />
  <xsl:param name="font" select="''" />
  <xsl:param name="format" select="''" />
  
<xsl:element name="current_date">
  <xsl:attribute name="font_style"><xsl:value-of select="$font"/></xsl:attribute>
  <xsl:attribute name="caption"><xsl:value-of select="$caption"/></xsl:attribute>
  <xsl:attribute name="format"><xsl:value-of select="$format"/></xsl:attribute>
</xsl:element>
</xsl:template>

<xsl:template name="CurrentTime">
  <xsl:param name="caption" select="''" />
  <xsl:param name="font" select="''" />
  <xsl:param name="format" select="''" />
  
<xsl:element name="current_time">
  <xsl:attribute name="font_style"><xsl:value-of select="$font"/></xsl:attribute>
  <xsl:attribute name="caption"><xsl:value-of select="$caption"/></xsl:attribute>
  <xsl:attribute name="format"><xsl:value-of select="$format"/></xsl:attribute>
</xsl:element>
</xsl:template>

<xsl:template name="CurrentDateTime">
  <xsl:param name="caption" select="''" />
  <xsl:param name="font" select="''" />
  <xsl:param name="format" select="''" />
  
<xsl:element name="current_datetime">
  <xsl:attribute name="font_style"><xsl:value-of select="$font"/></xsl:attribute>
  <xsl:attribute name="caption"><xsl:value-of select="$caption"/></xsl:attribute>
  <xsl:attribute name="format"><xsl:value-of select="$format"/></xsl:attribute>
</xsl:element>
</xsl:template>

</xsl:stylesheet>
