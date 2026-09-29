import 'package:flutter/material.dart';

enum PasswordStrengthLevel { empty, weak, fair, good, strong }

class PasswordStrengthResult {
  final PasswordStrengthLevel level;
  final double score; // 0.0 to 1.0
  final String label;
  final Color color;
  final bool hasMinLength;
  final bool hasUpperLower;
  final bool hasDigit;
  final bool hasSpecialChar;

  PasswordStrengthResult({
    required this.level,
    required this.score,
    required this.label,
    required this.color,
    required this.hasMinLength,
    required this.hasUpperLower,
    required this.hasDigit,
    required this.hasSpecialChar,
  });

  static PasswordStrengthResult evaluate(String password) {
    if (password.isEmpty) {
      return PasswordStrengthResult(
        level: PasswordStrengthLevel.empty,
        score: 0.0,
        label: 'Enter password',
        color: Colors.grey,
        hasMinLength: false,
        hasUpperLower: false,
        hasDigit: false,
        hasSpecialChar: false,
      );
    }

    final hasMinLength = password.length >= 8;
    final hasUpper = password.contains(RegExp(r'[A-Z]'));
    final hasLower = password.contains(RegExp(r'[a-z]'));
    final hasUpperLower = hasUpper && hasLower;
    final hasDigit = password.contains(RegExp(r'[0-9]'));
    final hasSpecialChar = password.contains(RegExp(r'[!@#\$%^&*(),.?":{}|<>_\-~+=]'));

    int metCount = 0;
    if (hasMinLength) metCount++;
    if (hasUpperLower) metCount++;
    if (hasDigit) metCount++;
    if (hasSpecialChar) metCount++;

    if (metCount <= 1) {
      return PasswordStrengthResult(
        level: PasswordStrengthLevel.weak,
        score: 0.25,
        label: 'Weak password',
        color: const Color(0xFFEF4444), // Bright Red
        hasMinLength: hasMinLength,
        hasUpperLower: hasUpperLower,
        hasDigit: hasDigit,
        hasSpecialChar: hasSpecialChar,
      );
    } else if (metCount == 2) {
      return PasswordStrengthResult(
        level: PasswordStrengthLevel.fair,
        score: 0.50,
        label: 'Fair password',
        color: const Color(0xFFF97316), // Orange
        hasMinLength: hasMinLength,
        hasUpperLower: hasUpperLower,
        hasDigit: hasDigit,
        hasSpecialChar: hasSpecialChar,
      );
    } else if (metCount == 3) {
      return PasswordStrengthResult(
        level: PasswordStrengthLevel.good,
        score: 0.75,
        label: 'Good password',
        color: const Color(0xFFEAB308), // Amber / Gold
        hasMinLength: hasMinLength,
        hasUpperLower: hasUpperLower,
        hasDigit: hasDigit,
        hasSpecialChar: hasSpecialChar,
      );
    } else {
      return PasswordStrengthResult(
        level: PasswordStrengthLevel.strong,
        score: 1.0,
        label: 'Strong password',
        color: const Color(0xFF10B981), // Emerald Green
        hasMinLength: hasMinLength,
        hasUpperLower: hasUpperLower,
        hasDigit: hasDigit,
        hasSpecialChar: hasSpecialChar,
      );
    }
  }
}
