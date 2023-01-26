<?php

class WmczTag {
    private $code;
    
    private $displayName;

    /**
     * @param string $code
     * @param string|null $displayName
     */
    public function __construct( $code, $displayName = null ) {
        $this->code = $code;
        $this->displayName = $displayName ?? $code;
    }

    /**
     * @return self
     */
    public static function newFromCode( string $tagCode ) {
        $tagAliases = WmczConfiguration::singleton()->get( 'tagAliases' );
        $tagDisplayNames = WmczConfiguration::singleton()->get( 'tagDisplayNames' );

        if ( array_key_exists( $tagCode, $tagAliases ) ) {
            $tagCode = $tagAliases[$tagCode];
        }

        return new WmczTag(
            $tagCode,
            $tagDisplayNames[$tagCode] ?? $tagCode
        );
    }

    public function __toString() {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getDisplayName() {
        return $this->displayName;
    }

    /**
     * @return string
     */
    public function getCode() {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getHTMLCheckbox( bool $selected ) {
        $selectedHTML = $selected ? ' checked' : '';
        return '<span class="wmcz-events-tag-span wmcz-tag-' . $this->getCode() . '"><input class="' . $this->getCode()
            . '" type="checkbox"' . $selectedHTML . ' name="tags[]" value="'
            . $this->getCode() . '" id="wmcz-events-tag-' . $this->getCode() . '">'
            . '<label for="wmcz-events-tag-' . $this->getCode() . '">' . $this->getDisplayName() . '</label></span>';
    }

    /**
     * @return string
     */
    public function getHTMLLink() {
        return 'TODO';
    }
}
