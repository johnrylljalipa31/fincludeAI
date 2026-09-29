import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:http_parser/http_parser.dart' as http_parser;
import '../config/api_config.dart';
import '../models/user_model.dart';
import '../models/credit_score_model.dart';
import '../models/loan_model.dart';

class AuthProvider with ChangeNotifier {
  String? _token;
  UserModel? _user;
  CreditScoreModel? _creditScore;
  LoanModel? _activeLoan;
  List<RepaymentScheduleItem> _repaymentSchedule = [];
  double _walletBalance = 24750.00;
  double _totalInflow = 38000.00;
  double _totalOutflow = 13250.00;
  bool _isLoading = false;
  String? _errorMessage;

  String? get token => _token;
  UserModel? get user => _user;
  CreditScoreModel? get creditScore => _creditScore;
  LoanModel? get activeLoan => _activeLoan;
  List<RepaymentScheduleItem> get repaymentSchedule => _repaymentSchedule;
  double get walletBalance => _walletBalance;
  double get totalInflow => _totalInflow;
  double get totalOutflow => _totalOutflow;
  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;
  bool get isAuthenticated => _token != null;

  Map<String, String> get _authHeaders => {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        if (_token != null) 'Authorization': 'Bearer $_token',
      };

  Future<bool> register({
    required String name,
    required String email,
    required String password,
    String? phoneNumber,
    String? idType,
    String? idNumber,
  }) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final response = await http.post(
        Uri.parse(ApiConfig.registerUrl),
        headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
        body: jsonEncode({
          'name': name,
          'email': email,
          'password': password,
          'phone_number': phoneNumber,
          'id_type': idType,
          'id_number': idNumber,
        }),
      );

      final data = jsonDecode(response.body);
      if (response.statusCode == 201 && data['status'] == 'success') {
        _token = data['token'];
        _user = UserModel.fromJson(data['user']);
        _isLoading = false;
        notifyListeners();
        return true;
      } else {
        _errorMessage = data['message'] ?? 'Registration failed';
        _isLoading = false;
        notifyListeners();
        return false;
      }
    } catch (e) {
      _errorMessage = 'Network connection error: $e';
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  Future<bool> login(String email, String password) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final response = await http.post(
        Uri.parse(ApiConfig.loginUrl),
        headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
        body: jsonEncode({
          'email': email,
          'password': password,
        }),
      );

      final data = jsonDecode(response.body);
      if (response.statusCode == 200 && data['status'] == 'success') {
        _token = data['token'];
        _user = UserModel.fromJson(data['user']);
        _isLoading = false;
        notifyListeners();
        await fetchDashboard();
        return true;
      } else {
        _errorMessage = data['message'] ?? 'Invalid credentials';
        _isLoading = false;
        notifyListeners();
        return false;
      }
    } catch (e) {
      _errorMessage = 'Login connection error: $e';
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  Future<Map<String, dynamic>> resetPassword(String email, String newPassword) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final response = await http.post(
        Uri.parse(ApiConfig.resetPasswordUrl),
        headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
        body: jsonEncode({
          'email': email,
          'password': newPassword,
        }),
      );

      final data = jsonDecode(response.body);
      _isLoading = false;
      notifyListeners();

      if (response.statusCode == 200 && data['status'] == 'success') {
        return {
          'success': true,
          'message': data['message'] ?? 'Password reset successfully! You may now sign in.',
        };
      } else {
        return {
          'success': false,
          'message': data['message'] ?? 'Failed to reset password.',
        };
      }
    } catch (e) {
      _isLoading = false;
      notifyListeners();
      return {
        'success': false,
        'message': 'Network error: $e',
      };
    }
  }

  Future<bool> submitConsent() async {
    if (_token == null) return false;
    _isLoading = true;
    notifyListeners();

    try {
      final response = await http.post(
        Uri.parse(ApiConfig.consentAgreeUrl),
        headers: _authHeaders,
        body: jsonEncode({
          'policy_version': '1.0',
          'legal_basis': 'RA 10173 - Philippine Data Privacy Act of 2012',
          'scope': 'income_verification,utility_history,credit_decisioning',
        }),
      );

      if (response.statusCode == 200) {
        _isLoading = false;
        notifyListeners();
        return true;
      }
    } catch (e) {
      _errorMessage = 'Error recording consent: $e';
    }

    _isLoading = false;
    notifyListeners();
    return false;
  }

  Future<String?> fetchSmileToken() async {
    if (_token == null) return null;

    try {
      final response = await http.post(
        Uri.parse(ApiConfig.smileTokenUrl),
        headers: _authHeaders,
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        return data['userToken'];
      }
    } catch (e) {
      debugPrint('Error fetching Smile user token: $e');
    }
    return null;
  }

  Future<void> fetchDashboard() async {
    if (_token == null) return;
    _isLoading = true;
    notifyListeners();

    try {
      final response = await http.get(
        Uri.parse(ApiConfig.dashboardUrl),
        headers: _authHeaders,
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (data['user'] != null) {
          _user = UserModel.fromJson(data['user']);
        }
        if (data['wallet_balance'] != null) {
          _walletBalance = (data['wallet_balance'] as num).toDouble();
        } else if (_user != null) {
          _walletBalance = _user!.walletBalance;
        }
        if (data['total_inflow'] != null) {
          _totalInflow = (data['total_inflow'] as num).toDouble();
        }
        if (data['total_outflow'] != null) {
          _totalOutflow = (data['total_outflow'] as num).toDouble();
        }
        if (data['credit_score'] != null) {
          _creditScore = CreditScoreModel.fromJson(data['credit_score']);
        }
        if (data['active_loan'] != null) {
          _activeLoan = LoanModel.fromJson(data['active_loan']);
        } else {
          _activeLoan = null;
        }
      }
    } catch (e) {
      debugPrint('Dashboard fetch error: $e');
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<bool> requestLoan({
    required double amount,
    required int tenureDays,
    required String purpose,
  }) async {
    if (_token == null) return false;
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final response = await http.post(
        Uri.parse(ApiConfig.loanApplyUrl),
        headers: _authHeaders,
        body: jsonEncode({
          'amount': amount,
          'tenure_days': tenureDays,
          'purpose': purpose,
        }),
      );

      final data = jsonDecode(response.body);
      if (response.statusCode == 201 && data['status'] == 'success') {
        _activeLoan = LoanModel.fromJson(data['loan']);
        _isLoading = false;
        notifyListeners();
        await fetchLoanSchedule();
        return true;
      } else {
        _errorMessage = data['message'] ?? 'Loan application could not be processed.';
        _isLoading = false;
        notifyListeners();
        return false;
      }
    } catch (e) {
      _errorMessage = 'Network error applying for loan: $e';
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  Future<void> fetchLoanSchedule() async {
    if (_token == null) return;

    try {
      final response = await http.get(
        Uri.parse(ApiConfig.loanActiveUrl),
        headers: _authHeaders,
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (data['loan'] != null) {
          _activeLoan = LoanModel.fromJson(data['loan']);
        }
        if (data['repayment_schedule'] != null) {
          var list = data['repayment_schedule'] as List;
          _repaymentSchedule = list.map((item) => RepaymentScheduleItem.fromJson(item)).toList();
        }
        notifyListeners();
      }
    } catch (e) {
      debugPrint('Error fetching loan schedule: $e');
    }
  }

  // Helper method to simulate webhook sync from UI
  Future<bool> triggerWebhookSync({String provider = 'Grab'}) async {
    try {
      final response = await http.post(
        Uri.parse(ApiConfig.webhookUrl),
        headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
        body: jsonEncode({
          'event': 'account.connected',
          'sourceUserId': _user?.id ?? 1,
          'userId': _user?.id ?? 1,
          'provider': provider,
        }),
      );
      if (response.statusCode == 200) {
        await fetchDashboard();
        return true;
      }
    } catch (e) {
      debugPrint('Webhook sync trigger error: $e');
    }
    return false;
  }

  Future<bool> updateProfile({String? name, String? phoneNumber}) async {
    if (_token == null) return false;
    try {
      final payload = <String, dynamic>{};
      if (name != null) payload['name'] = name;
      if (phoneNumber != null) payload['phone_number'] = phoneNumber;
      final response = await http.put(
        Uri.parse(ApiConfig.updateProfileUrl),
        headers: _authHeaders,
        body: jsonEncode(payload),
      );
      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (data['user'] != null) {
          _user = UserModel.fromJson(data['user']);
          notifyListeners();
          return true;
        }
      }
    } catch (e) {
      debugPrint('Profile update error: $e');
    }
    return false;
  }

  /// Upload or replace the user's profile picture.
  /// [imageBytes] — raw bytes of the image file.
  /// [filename]   — original filename (e.g. "photo.jpg").
  /// [mimeType]   — MIME type (e.g. "image/jpeg").
  Future<bool> uploadProfilePicture({
    required List<int> imageBytes,
    required String filename,
    String mimeType = 'image/jpeg',
  }) async {
    if (_token == null) return false;
    try {
      final request = http.MultipartRequest(
        'POST',
        Uri.parse(ApiConfig.uploadProfilePictureUrl),
      );
      request.headers.addAll({
        'Accept': 'application/json',
        'Authorization': 'Bearer $_token',
      });
      request.files.add(http.MultipartFile.fromBytes(
        'profile_picture',
        imageBytes,
        filename: filename,
        contentType: _parseMediaType(mimeType),
      ));

      final streamedResponse = await request.send();
      final response = await http.Response.fromStream(streamedResponse);

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (data['user'] != null) {
          _user = UserModel.fromJson(data['user']);
          notifyListeners();
          return true;
        }
      } else {
        debugPrint('Picture upload failed: ${response.body}');
      }
    } catch (e) {
      debugPrint('Picture upload error: $e');
    }
    return false;
  }

  /// Parse a MIME type string into a MediaType for multipart upload.
  dynamic _parseMediaType(String mimeType) {
    try {
      // http.MultipartFile accepts MediaType from package:http_parser
      // We build it manually to avoid an extra dependency.
      final parts = mimeType.split('/');
      if (parts.length == 2) {
        return http_parser.MediaType(parts[0], parts[1]);
      }
    } catch (_) {}
    return http_parser.MediaType('image', 'jpeg');
  }


  void logout() {
    _token = null;
    _user = null;
    _creditScore = null;
    _activeLoan = null;
    _repaymentSchedule = [];
    notifyListeners();
  }
}
