<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">
    <xsl:output method="text" indent="no"/>
    
    <xsl:template name="rpad">
        <xsl:param name="count" />
        <xsl:if test="$count > 0">
            <xsl:text> </xsl:text>
            <xsl:call-template name="rpad">
                <xsl:with-param name="count" select="$count - 1" />
            </xsl:call-template>
        </xsl:if>
    </xsl:template>
    
    <xsl:template name="print">
        <xsl:param name="text" />
        <xsl:param name="position" />
        <xsl:param name="length" />
        <xsl:value-of select="substring($text,$position,$length)"/>
        <xsl:call-template name="rpad">
            <xsl:with-param name="count" select="$length - string-length(substring($text,$position,$length))" />
        </xsl:call-template>
    </xsl:template>
    
    <xsl:template name="lpad">
        <xsl:param name="pad" />
        <xsl:param name="count" />
        <xsl:if test="$count > 0">
            <xsl:call-template name="lpad">
                <xsl:with-param name="pad" select="$pad" />
                <xsl:with-param name="count" select="$count - 1" />
            </xsl:call-template>
            <xsl:value-of select="$pad" />
        </xsl:if>
    </xsl:template>
    
    <xsl:template name="print-leftpad">
        <xsl:param name="text" />
        <xsl:param name="position" />
        <xsl:param name="length" />
        <xsl:param name="pad" />        
        <xsl:call-template name="lpad">
            <xsl:with-param name="pad" select="$pad" />
            <xsl:with-param name="count" select="$length - string-length(substring($text,$position,$length))" />            
        </xsl:call-template>
        <xsl:value-of select="substring($text,$position,$length)"/>
    </xsl:template>
    
    <xsl:template match="/">
        <xsl:for-each select="/table/record">
            <!--<xsl:text>PLU:</xsl:text><xsl:value-of select="substring(@FASTCODE,1, 6)" /><xsl:value-of select="concat(substring({FASTCODE},1,6))"/><xsl:text>&#xd;&#xa;</xsl:text>-->
            <xsl:call-template name="print-leftpad">
                <xsl:with-param name="text" select="@FASTCODE" />
                <xsl:with-param name="position">1</xsl:with-param>
                <xsl:with-param name="length">6</xsl:with-param>
                <xsl:with-param name="pad">0</xsl:with-param>
            </xsl:call-template>
            <xsl:text><!--GME:</xsl:text><xsl:value-of = -->0001<!--/><xsl:text>&#xd;&#xa;--></xsl:text>
            <!--<xsl:text>PRZ:</xsl:text>--><xsl:value-of select="translate(format-number(@PRICE, '0000.00'), '.', '')"/><!--<xsl:text>&#xd;&#xa;</xsl:text>-->
            <!--<xsl:text>BAR:</xsl:text><xsl:value-of select="@BARCODE"/><xsl:text>&#xd;&#xa;</xsl:text>-->
            <xsl:call-template name="print">
                <xsl:with-param name="text" select="@BARCODE" />
                <xsl:with-param name="position">1</xsl:with-param>
                <xsl:with-param name="length">7</xsl:with-param>
            </xsl:call-template>
            <xsl:text><!--STR:</xsl:text><xsl:value-of = -->00001166<!--/><xsl:text>&#xd;&#xa;--></xsl:text>
            <!--<xsl:text>DES:</xsl:text><xsl:value-of select="substring(@DESCRIPTION,1,20)"/><xsl:value-of select="concat(substring(DESCRIPTION,1,20))"/><xsl:text>&#xd;&#xa;</xsl:text>-->
            <xsl:call-template name="print">
                <xsl:with-param name="text" select="@DESCRIPTION" />
                <xsl:with-param name="position">1</xsl:with-param>
                <xsl:with-param name="length">20</xsl:with-param>
            </xsl:call-template>
            <xsl:text><!--STR:</xsl:text><xsl:value-of = -->|<!--/><xsl:text>&#xd;&#xa;--></xsl:text>
            <!--<xsl:text>DES:</xsl:text><xsl:value-of select="substring(@DESCRIPTION,21,20)"/><xsl:value-of select="concat(substring(DESCRIPTION,21,20))"/><xsl:text>&#xd;&#xa;</xsl:text>-->
            <xsl:call-template name="print">
                <xsl:with-param name="text" select="@DESCRIPTION" />
                <xsl:with-param name="position">21</xsl:with-param>
                <xsl:with-param name="length">20</xsl:with-param>
            </xsl:call-template>
            <!-- xsl:text>0000000000000000100@000000A&#xd;&#xa;</xsl:text -->
            <xsl:text>0000000000000000100</xsl:text>
            <xsl:call-template name="print">
                <xsl:with-param name="text" select="@UNIMIS" />
                <xsl:with-param name="position">1</xsl:with-param>
                <xsl:with-param name="length">1</xsl:with-param>
            </xsl:call-template>
            <xsl:text>000000A&#xd;&#xa;</xsl:text>
        </xsl:for-each>
    </xsl:template>
</xsl:stylesheet>



