<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">

<xsl:template name="CrLf">
  <xsl:text>&#xd;&#xa;</xsl:text>
</xsl:template>

<xsl:template name="RepeatString">
  <xsl:param name="string" select="''" />
  <xsl:param name="times" select="1" />
  <xsl:param name="addcrlf" select="1" />
  
  <xsl:if test="number($times) &gt; 0">
    <xsl:value-of select="$string" />
    <xsl:call-template name="RepeatString">
      <xsl:with-param name="string" select="$string" />
      <xsl:with-param name="times"  select="$times - 1" />
      <xsl:with-param name="addcrlf" select="0" />
    </xsl:call-template>
    <xsl:if test="$addcrlf=1">
      <xsl:text>&#xd;&#xa;</xsl:text>
    </xsl:if>
  </xsl:if>
</xsl:template>

<xsl:template name="CenterString">
  <xsl:param name="string" select="''" />
  <xsl:param name="length" select="1" />
  <xsl:param name="addcrlf" select="1" />
  <xsl:if test="number($length) &gt; 0">
    <xsl:call-template name="RepeatString">
      <xsl:with-param name="string" select="' '" />
      <xsl:with-param name="times" select="($length - string-length($string)) div 2" />
      <xsl:with-param name="addcrlf" select="0" />
    </xsl:call-template>
    <xsl:value-of select="$string" />
    <xsl:if test="$addcrlf=1">
      <xsl:text>&#xd;&#xa;</xsl:text>
    </xsl:if>
  </xsl:if>
</xsl:template>

<xsl:template name="RightString">
  <xsl:param name="string" select="''" />
  <xsl:param name="length" select="1" />
  <xsl:param name="addcrlf" select="1" />
  <xsl:if test="number($length) &gt; 0">
    <xsl:call-template name="RepeatString">
      <xsl:with-param name="string" select="' '" />
      <xsl:with-param name="times" select="($length - string-length($string))" />
      <xsl:with-param name="addcrlf" select="0" />
    </xsl:call-template>
    <xsl:value-of select="$string" />
    <xsl:if test="$addcrlf=1">
      <xsl:text>&#xd;&#xa;</xsl:text>
    </xsl:if>
  </xsl:if>
</xsl:template>

<xsl:template name="LeftString">
  <xsl:param name="string" select="''" />
  <xsl:param name="length" select="1" />
  <xsl:param name="addcrlf" select="1" />
  <xsl:if test="number($length) &gt; 0">
    <xsl:value-of select="$string" />
    <xsl:call-template name="RepeatString">
      <xsl:with-param name="string" select="' '" />
      <xsl:with-param name="times" select="($length - string-length($string))" />
      <xsl:with-param name="addcrlf" select="0" />
    </xsl:call-template>
    <xsl:if test="$addcrlf=1">
      <xsl:text>&#xd;&#xa;</xsl:text>
    </xsl:if>
  </xsl:if>
</xsl:template>

<xsl:template name="PrintSplitList">
  <xsl:param name="string" select="''" />
  <xsl:param name="separator" select="','" />
  <xsl:if test="string-length($string) &gt; 0">
    <xsl:choose>
      <xsl:when test="not(contains($string, $separator))">
        <xsl:value-of select="normalize-space($string)" />
      </xsl:when>
      <xsl:otherwise>
        <xsl:value-of select="substring-before($string, $separator)" />
      </xsl:otherwise>
    </xsl:choose><xsl:text>&#xd;&#xa;</xsl:text>
    <xsl:call-template name="PrintSplitList">
      <xsl:with-param name="string" select="substring-after($string, $separator)" />
      <xsl:with-param name="separator" select="','" />
    </xsl:call-template>
  </xsl:if>  
</xsl:template>

<xsl:template name="PrintDate">
  <xsl:param name="date" select="''" />
  <xsl:value-of select="concat(substring($date, 7, 2), '/', substring($date, 5, 2), '/', substring($date, 1, 4))"/>
</xsl:template>

<xsl:template name="PrintTime">
  <xsl:param name="date" select="''" />
  <xsl:value-of select="concat(substring($date, 9, 2), ':', substring($date, 11, 2))"/>
</xsl:template>

<xsl:template name="PrintTimeSec">
  <xsl:param name="date" select="''" />
  <xsl:value-of select="concat(substring($date, 9, 2), ':', substring($date, 11, 2), ':', substring($date, 13, 2))"/>
</xsl:template>

<xsl:template name="PrintDateTime">
  <xsl:param name="date" select="''" />
  <xsl:value-of select="concat(substring($date, 7, 2), '/', substring($date, 5, 2), '/', substring($date, 1, 4), ' ', substring($date, 9, 2), ':', substring($date, 11, 2))"/>
</xsl:template>

<xsl:template name="PrintDateTimeSec">
  <xsl:param name="date" select="''" />
  <xsl:value-of select="concat(substring($date, 7, 2), '/', substring($date, 5, 2), '/', substring($date, 1, 4), ' ', substring($date, 9, 2), ':', substring($date, 11, 2), ':', substring($date, 13, 2))"/>
</xsl:template>

</xsl:stylesheet>
