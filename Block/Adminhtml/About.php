<?php
/**
 * @category  Hevelop
 * @package   Hevelop_FacebookPixel
 * @author    Hevelop
 * @copyright Copyright (c) 2016 Hevelop (http://hevelop.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License
 */

namespace Hevelop\FacebookPixel\Block\Adminhtml;

use Magento\Framework\Data\Form\Element\AbstractElement;

class About extends \Magento\Backend\Block\AbstractBlock implements
    \Magento\Framework\Data\Form\Element\Renderer\RendererInterface
{
    /**
     * @var \Hevelop\FacebookPixel\Helper\Data
     */
    public $helper;
    
    /**
     * Constructor
     *
     * @param \Hevelop\FacebookPixel\Helper\Data $helper
     */
    public function __construct(\Hevelop\FacebookPixel\Helper\Data $helper)
    {
        $this->helper = $helper;
    }
    
    /**
     * Retrieve element HTML markup.
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $element  = null;
        $version  = $this->helper->getExtensionVersion();
        $logopath = 'https://hevelop.com/wp-content/themes/hevelop/dist/images/logo.png';
        $html     = <<<HTML
<div style="background: url('$logopath') no-repeat scroll 15px 15px #f8f8f8; 
border:1px solid #ccc; min-height:100px; margin:5px 0; 
padding:15px 15px 15px 68px;">
<p>
<strong>Hevelop Facebook Pixel Extension v$version</strong><br />
</p>
<p>
Website: <a href="http://hevelop.com" target="_blank">hevelop.com</a>
<br />Like, share and follow us on 
<a href="https://www.facebook.com/hevelop" target="_blank">Facebook</a>, 
<a href="https://github.com/Hevelop" target="_blank">Github</a> 
<br />
If you have any questions send email at 
<a href="mailto:support@hevelop.com">support@hevelop.com</a>.
</p>
</div>
HTML;
        return $html;
    }
}
