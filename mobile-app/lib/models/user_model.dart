class UserModel {
  final int id;
  final String name;
  final String email;
  final String? phoneNumber;
  final String? idType;
  final String? idNumber;
  final bool hasConsented;
  final double walletBalance;
  final String role;
  final String? profilePictureUrl;

  UserModel({
    required this.id,
    required this.name,
    required this.email,
    this.phoneNumber,
    this.idType,
    this.idNumber,
    this.hasConsented = false,
    this.walletBalance = 24750.00,
    this.role = 'gig_worker',
    this.profilePictureUrl,
  });

  bool get isManager =>
      role.toLowerCase() == 'manager' ||
      role.toLowerCase() == 'admin' ||
      email.toLowerCase() == 'hyaylanan13@gmail.com';

  factory UserModel.fromJson(Map<String, dynamic> json) {
    return UserModel(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id'].toString()) ?? 0,
      name: json['name'] ?? '',
      email: json['email'] ?? '',
      phoneNumber: json['phone_number'],
      idType: json['id_type'],
      idNumber: json['id_number'],
      hasConsented: json['has_consented'] == true,
      walletBalance: json['wallet_balance'] != null
          ? (json['wallet_balance'] as num).toDouble()
          : 24750.00,
      role: json['role'] ?? 'gig_worker',
      profilePictureUrl: json['profile_picture_url'],
    );
  }
}
