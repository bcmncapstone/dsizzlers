<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contract Expiry Reminder</title>
</head>
<body style="margin: 0; padding: 0; background: #f5f7fb; font-family: Arial, sans-serif; color: #1f2937;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="padding: 24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width: 620px; background: #ffffff; border-radius: 10px; overflow: hidden; border: 1px solid #e5e7eb;">
                    <tr>
                        <td style="background: #0f172a; color: #ffffff; padding: 18px 24px;">
                            <h1 style="margin: 0; font-size: 20px;">Contract Expiry Reminder</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 24px;">
                            <p style="margin: 0 0 16px 0; font-size: 15px;">
                                Hello {{ trim(($branch->first_name ?? '') . ' ' . ($branch->last_name ?? '')) ?: 'Franchisee' }},
                            </p>

                            <p style="margin: 0 0 16px 0; font-size: 15px; line-height: 1.6;">
                                This is a reminder that your branch contract is set to expire in
                                <strong>{{ $daysRemaining }} day{{ $daysRemaining === 1 ? '' : 's' }}</strong>.
                            </p>

                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin: 0 0 18px 0; border-collapse: collapse;">
                                <tr>
                                    <td style="padding: 10px 12px; border: 1px solid #e5e7eb; background: #f8fafc; width: 45%;"><strong>Branch Location</strong></td>
                                    <td style="padding: 10px 12px; border: 1px solid #e5e7eb;">{{ $branch->location ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 10px 12px; border: 1px solid #e5e7eb; background: #f8fafc;"><strong>Contract Expiration Date</strong></td>
                                    <td style="padding: 10px 12px; border: 1px solid #e5e7eb;">
                                        {{ optional($branch->contract_expiration)->format('F j, Y') ?? 'N/A' }}
                                    </td>
                                </tr>
                            </table>

                            <p style="margin: 0; font-size: 14px; line-height: 1.6;">
                                Please contact the owner or franchisor to discuss your contract renewal.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 16px 24px; font-size: 12px; color: #6b7280; border-top: 1px solid #e5e7eb; background: #f8fafc;">
                            This is an automated reminder from {{ config('app.name') }}.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
