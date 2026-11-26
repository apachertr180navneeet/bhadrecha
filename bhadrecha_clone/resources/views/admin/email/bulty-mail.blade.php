<table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="box-sizing:border-box;">
    <tbody>
        <tr>
            <td align="left" style="">
                <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin:0;padding:0;width:100%">
                    <tbody>
                        <tr>
                            <td width="100%" cellpadding="0" cellspacing="0" style="">
                                <table align="left" cellpadding="0" cellspacing="0" role="presentation">
                                    <tbody>
                                        <tr align="left">
                                            <td align="left">
                                                <h3>Bilty Details - {{ $bulty->lr_no }}</h3>
                                                <p><strong>LR No:</strong> {{ $bulty->lr_no }}</p>
                                                <p><strong>Date:</strong> {{ $bulty->lr_date?->format('d M Y') ?? '-' }}</p>
                                                <p><strong>From:</strong> {{ $bulty->originCity?->name ?? $bulty->from_city ?? '-' }}</p>
                                                <p><strong>To:</strong> {{ $bulty->destinationCity?->name ?? $bulty->to_city ?? '-' }}</p>
                                                <p><strong>Consignor:</strong> {{ $bulty->consignor?->name ?? '-' }}</p>
                                                <p><strong>Consignee:</strong> {{ $bulty->consignee?->name ?? '-' }}</p>
                                                <p><strong>Vehicle:</strong> {{ $bulty->vehicle?->vehicle_number ?? '-' }}</p>
                                                <p>Please find the attached PDF for detailed bilty information.</p>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </td>
        </tr>
    </tbody>
</table>
