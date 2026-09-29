import 'package:flutter/foundation.dart';

class ApiConfig {
  // Default to 127.0.0.1:8001 for local development, or 10.0.2.2:8001 on Android Emulator
  static String get baseUrl {
    if (kIsWeb) {
      return 'http://127.0.0.1:8001/api';
    }
    // For native Android emulator, use 10.0.2.2; for physical device or desktop use 127.0.0.1 or local network IP
    if (defaultTargetPlatform == TargetPlatform.android) {
      return 'http://10.0.2.2:8001/api';
    }
    return 'http://127.0.0.1:8001/api';
  }

  // Auth endpoints
  static String get registerUrl => '$baseUrl/auth/register';
  static String get loginUrl => '$baseUrl/auth/login';
  static String get forgotPasswordUrl => '$baseUrl/auth/forgot-password';
  static String get resetPasswordUrl => '$baseUrl/auth/reset-password';
  static String get meUrl => '$baseUrl/auth/me';
  static String get updateProfileUrl => '$baseUrl/auth/profile';
  static String get uploadProfilePictureUrl => '$baseUrl/auth/profile/picture';

  // Consent endpoint (RA 10173)
  static String get consentAgreeUrl => '$baseUrl/consent/agree';
  static String get consentStatusUrl => '$baseUrl/consent/status';

  // Smile Wink Token endpoint
  static String get smileTokenUrl => '$baseUrl/smile/token';

  // Webhook Mock Trigger for Testing
  static String get webhookUrl => '$baseUrl/webhook/smile';

  // Dashboard & Loans
  static String get dashboardUrl => '$baseUrl/dashboard';
  static String get loanApplyUrl => '$baseUrl/loan/apply';
  static String get loanActiveUrl => '$baseUrl/loan/active';

  // Manager endpoints
  static String get managerQueueUrl => '$baseUrl/manager/queue';
  static String managerApplicantUrl(int id) => '$baseUrl/manager/applicant/$id';
  static String managerLoanDecisionUrl(int id) => '$baseUrl/manager/loan/$id/decision';
  static String managerDeleteLoanUrl(int id) => '$baseUrl/manager/loan/$id';
}
