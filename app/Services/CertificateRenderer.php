<?php

namespace App\Services;

class CertificateRenderer
{
    /**
     * Main function: Builds the full SMCS-style HTML
     */
    public function render($unit, $parsedExcelData)
    {
        return
            $this->sectionCustomerDetails($unit, $parsedExcelData) .
            $this->sectionSpecification($unit, $parsedExcelData) .
            $this->sectionDates($unit, $parsedExcelData) .
            $this->sectionAuthorization($unit) .
            $this->sectionMethod($parsedExcelData) .
            $this->sectionEnvironment($parsedExcelData) .
            $this->sectionTraceability($parsedExcelData) .
            $this->sectionCalibrationResults($parsedExcelData) .
            $this->sectionUncertainty() .
            $this->sectionRemarks();
    }

    private function sectionCustomerDetails($unit, $data)
    {
        return "
            <br><b>1. CUSTOMER DETAILS</b>
            <div class='ml-16'>
                <span>Customer:</span><br>
                <span><i>{$data['customer_name']}</i></span><br>

                <span>Address:</span><br>
                <span><i>{$data['customer_address']}</i></span><br>

                <span>End-user:</span><br>
                <span><i>{$data['end_user']}</i></span><br>

                <span>End-user Address:</span><br>
                <span><i>{$data['end_user_address']}</i></span><br>
            </div>
        ";
    }

    private function sectionSpecification($unit, $data)
    {
        return "
            <br><b>2. SPECIFICATION OF UNIT UNDER TEST</b>
            <div class='ml-16'>
                <table class='vertical-top'>
                    <tr><td width='150'>Unit Under Test:</td><td><i>{$unit->name}</i></td></tr>
                    <tr><td width='150'>Range:</td><td>{$unit->range}</td></tr>
                    <tr><td width='150'>Manufacturer:</td><td>{$unit->manufacturer}</td></tr>
                    <tr><td width='150'>Model:</td><td>{$unit->model}</td></tr>
                    <tr><td width='150'>Serial Number:</td><td>{$unit->serial_number}</td></tr>
                    <tr><td width='150'>Identification:</td><td>{$unit->identification}</td></tr>
                </table>
            </div>
        ";
    }

    private function sectionDates($unit, $data)
    {
        return "
            <br><b>3. DATE OF ACTIVITIES</b>
            <div class='ml-16'>
                <table class='vertical-top'>
                    <tr><td width='150'>Date Calibrated:</td><td>{$data['date_calibrated']}</td></tr>
                    <tr><td width='150'>Recommended Due:</td><td><i>{$data['recommended_due']}</i></td></tr>
                    <tr><td width='150'>Date Issued:</td><td>{$data['date_issued']}</td></tr>
                </table>
            </div>
        ";
    }

    private function sectionAuthorization($unit)
    {
        return "
            <br><b>4. AUTHORIZATION</b>
            <div class='ml-16'>
                <table class='page-break'>
                    <tr>
                        <td>
                            <table>
                                <tr>
                                    <td>Calibrated by:</td>
                                    <td>Reviewed and Authorized by:</td>
                                </tr>
                                <tr>
                                    <td><br><img src='/storage/signature/{$unit->analyst_signature}' style='width:160px'><br>{$unit->analyst_name}</td>
                                    <td><br><img src='/storage/signature/{$unit->approver_signature}' style='width:160px'><br>{$unit->approver_name}</td>
                                </tr>
                            </table>
                        </td>
                        <td width='200' class='text-center'>
                            <img src='{$unit->qr_path}'><br>
                            <span style='font-size:9pt;'>QR code verification note</span>
                        </td>
                    </tr>
                </table>
            </div>
        ";
    }

    private function sectionMethod($data)
    {
        return "
            <br><b>5. METHOD</b>
            <div class='ml-16'>
                <table class='vertical-top'>
                    <tr><td width='150'>Method Used:</td><td>{$data['method_used']}</td></tr>
                    <tr><td width='150'>Type of Calibration:</td><td>{$data['calibration_type']}</td></tr>
                    <tr><td width='150'>Location:</td><td>{$data['location']}</td></tr>
                </table>
            </div>
        ";
    }

    private function sectionEnvironment($data)
    {
        return "
            <br><b>6. ENVIRONMENTAL CONDITIONS</b>
            <div class='ml-16'>
                <table class='vertical-top'>
                    <tr><td width='150'>Temperature:</td><td>{$data['environment_temp']}</td></tr>
                    <tr><td width='150'>Relative Humidity:</td><td>{$data['environment_rh']}</td></tr>
                </table>
            </div>
        ";
    }

    private function sectionTraceability($data)
    {
        return "
            <br><b>7. TRACEABILITY</b>
            <div class='ml-16'>
                {$data['traceability_table_html']}
            </div>
        ";
    }

    private function sectionCalibrationResults($data)
    {
        return "
            <br><b>8. CALIBRATION RESULTS</b>
            <div class='ml-16'>
               {$data['calibration_table_html']}
            </div>
        ";
    }

    private function sectionUncertainty()
    {
        return "
            <br><b>9. UNCERTAINTY OF MEASUREMENTS</b>
            <div class='ml-16'>
                <span>The uncertainty stated is the expanded uncertainty ... approx 95% confidence.</span>
            </div>
        ";
    }

    private function sectionRemarks()
    {
        return "
            <br><b>10. REMARKS</b>
            <div class='ml-16'>
               <ol class='pl-20 m-0' type='a'>
                    <li><p>The context in italic form is derived from customer data.</p></li>
                    <li><p>No adjustments were performed.</p></li>
                    <li><p>Results refer only to the submitted item.</p></li>
                    <li><p>Recalibration interval depends on end-user use.</p></li>
                    <li><p>Temperature quotes refer to ITS-90.</p></li>
               </ol>
            </div>
        ";
    }
}
