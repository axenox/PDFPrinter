<?php
namespace axenox\PDFPrinter\Actions\Traits;

use Dompdf\Dompdf;
use axenox\PDFPrinter\Interfaces\Actions\iCreatePdf;


trait iCreatePdfTrait
{
    private $orientation = 'landscape';
    
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
     * Returns dompdf->output() stream to save in a file.
     *
     * @param string $contentHtml
     * @return unknown
     */
    public function createPdf(string $contentHtml)
    {
        // instantiate and use the dompdf class
        $dompdf = new Dompdf();
        
        // Add callbacks to customize rendering.
        $dompdf->setCallbacks([
            // Skip rendering frames with "fixed" positioning on the first page. This mostly results in automatic
            // headers and footers only being rendered on the second page and beyond.
            // TODO geb 2026-03-24: We might need a more control over what pages fixed frames will be rendered on. 
            [
                'event' => 'begin_frame',
                'f' => function ($frame, $canvas, $fontMetrics) {
                    $style = $frame->get_style();
                    if ($canvas->get_page_number() === 1 && $style->position === "fixed") {
                        $style->set_used("display", "none");
                    }
                }
            ]
        ]);
        
        $options = $dompdf->getOptions();
        $options->setIsRemoteEnabled(true);
        $options->setIsPhpEnabled(true);
        $dompdf->loadHtml($contentHtml);
        
        // (Optional) Setup the paper size and orientation
        $dompdf->setPaper('A4', $this->getOrientation());
        
        // Render the HTML as PDF
        $dompdf->render();
        return $dompdf->output();
    }
    
}