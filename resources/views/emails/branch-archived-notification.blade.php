<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Archived</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f7f7f7; font-family: Arial, sans-serif; color: #333333;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f7f7f7; padding: 24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width: 600px; background-color: #ffffff; border-radius: 12px; overflow: hidden;">
                    <tr>
                        <td style="background-color: #c0392b; padding: 24px; color: #ffffff;">
                            <h1 style="margin: 0; font-size: 24px; font-weight: 700;">Your Account Has Been Archived</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 24px;">
                            <p style="margin-top: 0; line-height: 1.6;">
                                Hello {{ trim(($branch->first_name ?? '') . ' ' . ($branch->last_name ?? '')) ?: 'Franchisee' }},
                            </p>
                            <p style="line-height: 1.6;">
                                Your D Sizzlers franchisee account has been archived by the admin. You will no longer be able to log in while the account remains archived.
                            </p>
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin: 24px 0; border: 1px solid #eeeeee; border-radius: 8px;">
                                <tr>
                                    <td style="padding: 12px 16px; border-bottom: 1px solid #eeeeee; font-weight: 700; width: 40%;">Branch Location</td>
                                    <td style="padding: 12px 16px; border-bottom: 1px solid #eeeeee;">{{ $branch->location ?: 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px 16px; border-bottom: 1px solid #eeeeee; font-weight: 700;">Email</td>
                                    <td style="padding: 12px 16px; border-bottom: 1px solid #eeeeee;">{{ $branch->email ?: 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px 16px; font-weight: 700;">Contract Expiration</td>
                                    <td style="padding: 12px 16px;">{{ optional($branch->contract_expiration)->format('F j, Y') ?? 'N/A' }}</td>
                                </tr>
                            </table>
                            <p style="line-height: 1.6; margin-bottom: 0;">
                                Please contact the admin if you believe this was done in error or if you need help with reactivation.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
