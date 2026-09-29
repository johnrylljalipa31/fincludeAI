# Google Play Store Compliance & RA 10173 Policy Documentation

This document outlines the mandatory legal, privacy, and technical configurations required to publish **Finclude-AI** on the Google Play Store under Google's Financial Services and Personal Loans Policy, in full alignment with Republic Act No. 10173 (Philippine Data Privacy Act of 2012).

---

## 1. Google Play Financial Services Policy Compliance

Google Play enforces strict requirements for apps offering personal micro-loans.

### A. Repayment Period Requirements
- **Strict Prohibition**: Google Play does **NOT** permit personal loans that require repayment in full within **60 days** or less from the date the loan is issued (payday loan prohibition).
- **Finclude-AI Implementation**:
  * Minimum Loan Tenure: **61 Days** (approx. 2 months).
  * Maximum Loan Tenure: **180 Days** (approx. 6 months).
  * No short-term payday loans are offered.

### B. Annual Percentage Rate (APR) Transparency
- All loan calculators and promotional banners must disclose the Maximum Annual Percentage Rate (APR), which includes interest plus all recurring finance fees.
- **Finclude-AI Implementation**:
  * Fixed APR: **24.0% per annum** (2.0% nominal monthly rate).
  * No hidden processing fees or surprise penalty deductions.

### C. Representative Loan Repayment Example
The following representative example is prominently embedded in the app's disclosures:

| Parameter | Value |
|---|---|
| **Loan Principal Amount** | ₱10,000.00 |
| **Loan Tenure** | 90 Days (3 Months) |
| **Annual Percentage Rate (APR)** | 24.0% p.a. |
| **Calculated Interest** | $\text{PHP } 10,000 \times \frac{24\%}{365} \times 90 = \mathbf{₱591.78}$ |
| **Total Amount Repayable** | $\mathbf{₱10,591.78}$ |
| **Monthly Installment (3 months)** | $\mathbf{₱3,530.59}$ |

---

## 2. Republic Act 10173 (Data Privacy Act of 2012)

### A. Explicit Informed Consent
- Prior to accessing any Open Banking or financial data via Smile API, the user must view and accept the statutory consent modal (`ConsentModalScreen`).
- Every consent record is permanently logged in the backend `consents` table with:
  * User ID
  * Timestamp of agreement (`agreed_at`)
  * IP Address (`ip_address`)
  * Policy Version (`policy_version`)
  * Specific scope (`income_verification, utility_history, credit_decisioning`)

### B. Data Minimization & Retention
- Only transactional fields relevant to the PSITE feature model (cash flow velocity, burn rate, utility payments, DTI) are extracted.
- Users retain the right to revoke consent and request data deletion by contacting `dpo@finclude.ai`.

---

## 3. Security Configuration for Release Builds

### A. HTTPS-Only Enforcement (`network_security_config.xml`)
Ensure non-cleartext traffic is blocked in `mobile-app/android/app/src/main/res/xml/network_security_config.xml`:
```xml
<?xml version="1.0" encoding="utf-8"?>
<network-security-config>
    <base-config cleartextTrafficPermitted="false">
        <trust-anchors>
            <certificates src="system" />
        </trust-anchors>
    </base-config>
</network-security-config>
```

### B. Code Obfuscation with ProGuard (`proguard-rules.pro`)
Protect sensitive scoring business logic and API endpoints when building release artifacts:
```proguard
# Flutter ProGuard Rules
-keep class io.flutter.app.** { *; }
-keep class io.flutter.plugin.**  { *; }
-keep class io.flutter.util.**  { *; }
-keep class io.flutter.view.**  { *; }
-keep class io.flutter.** { *; }
-keep class io.flutter.plugins.**  { *; }
-dontwarn io.flutter.embedding.**
-keepattributes *Annotation*
```

### C. Building the Production Release Bundle
```powershell
cd c:\Finclude-AI\mobile-app
flutter build appbundle --release --obfuscate --split-debug-info=./build/app/outputs/symbols
```
