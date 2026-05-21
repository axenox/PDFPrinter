<?php
namespace axenox\PDFPrinter\Actions\Traits;

use Dompdf\Dompdf;
use axenox\PDFPrinter\Interfaces\Actions\iCreatePdf;


trait iCreatePdfTrait
{
    private $orientation = 'landscape';
    private bool $hideFixedElementsOnFirstPage = true;

    /**
     * @uxon-property orientation
     * @uxon-type [portrait,landscape]
     * @uxon-required true
     *
     * @param string $value
     * @return iCreatePdfTrait
     */
    public function setOrientation(string $value) : iCreatePdf
    {
        $this->orientation = $value;
        return $this;
    }

    public function getOrientation() : string
    {
        return $this->orientation;
    }

    /**
     * Configure, whether elements with the style option `"position": "fixed"` should be hidden
     * on the first page of the PDF. 
     * 
     * This setting is TRUE by default, to prevent footers and headers from being rendered on the first page.
     * 
     * @uxon-property hide_fixed_elements_on_first_page
     * @uxon-type boolean
     * 
     * @param bool $value
     * @return iCreatePdf
     */
    public function setHideFixedElementsOnFirstPage(bool $value): iCreatePdf
    {
        $this->hideFixedElementsOnFirstPage = $value;
        return $this;
    }

    /**
     * @return bool
     */
    public function getHideFixedElementsOnFirstPage(): bool
    {
        return $this->hideFixedElementsOnFirstPage;
    }

    /**
     * Returns dompdf->output() stream to save in a file.
     *
     * @param string $contentHtml
     * @return unknown
     */
    public function createPdf(string $contentHtml)
    {
        // instantiate and use the dompdf class
        $dompdf = new Dompdf();
        
        $callbacks = [];
        
        if($this->getHideFixedElementsOnFirstPage()) {
            // Skip rendering frames with "fixed" positioning on the first page. This mostly results in automatic
            // headers and footers only being rendered on the second page and beyond.
            // TODO geb 2026-03-24: We might need a more control over what pages fixed frames will be rendered on. 
            $callbacks[] = [
                'event' => 'begin_frame',
                'f' => function ($frame, $canvas, $fontMetrics) {
                    $style = $frame->get_style();
                    if ($canvas->get_page_number() === 1 && $style->position === "fixed") {
                        $style->set_used("display", "none");
                    }
                }
            ];
        }
        
        if(!empty($callbacks)) {
            // Add callbacks to customize rendering.
            $dompdf->setCallbacks($callbacks);
        }
        
        $options = $dompdf->getOptions();
        $options->setIsRemoteEnabled(true);
        $options->setIsPhpEnabled(true);
        //enable font subsetting in Dompdf to reduce file sizes by only embedding the characters used ithe document
        $options->set('enable_font_subsetting', true);
        $dompdf->loadHtml($contentHtml);
        
        // (Optional) Setup the paper size and orientation
        $dompdf->setPaper('A4', $this->getOrientation());
        
        // Render the HTML as PDF
        $dompdf->render();
        return $dompdf->output();
    }

}