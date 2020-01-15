<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

    <xsl:template name="print-new-line">
        <xsl:param name="string"/>
        <xsl:if test="starts-with($string, ' ')">
            <xsl:call-template name="print-new-line">
                <xsl:with-param name="string" select="substring-after($string,' ')"/>
            </xsl:call-template>
        </xsl:if>
        <xsl:choose>
            <xsl:when test="string-length($string) > 0">
                <xsl:value-of select="$string"/>
            </xsl:when>
            <xsl:otherwise>
                &#160;
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>

    <xsl:template name="print-fiscal-code">
        <xsl:param name="fiscal_code_a"/>
        <xsl:param name="fiscal_code_b"/>
        <xsl:choose>
            <xsl:when test="$fiscal_code_a != '' and $fiscal_code_b != ''">
                <xsl:choose>
                    <xsl:when test="$fiscal_code_a = $fiscal_code_b">
                        <xsl:value-of select="concat('P.IVA e codice fiscale ', $fiscal_code_a)"/>
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:value-of select="concat('P.IVA ', $fiscal_code_a, ' Cod. fiscale ', $fiscal_code_b)"/>
                    </xsl:otherwise>
                </xsl:choose>
            </xsl:when>
            <xsl:otherwise>
                <xsl:choose>
                    <xsl:when test="$fiscal_code_a != ''">
                        <xsl:value-of select="concat('P.IVA ', $fiscal_code_a)"/>
                    </xsl:when>
                    <xsl:when test="$fiscal_code_b != ''">
                        <xsl:value-of select="concat('Codice fiscale ', $fiscal_code_b)"/>
                    </xsl:when>
                    <xsl:otherwise>
                            &#160;
                        </xsl:otherwise>
                </xsl:choose>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>
</xsl:stylesheet>
