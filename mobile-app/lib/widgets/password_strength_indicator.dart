import 'package:flutter/material.dart';
import '../utils/password_strength.dart';

class PasswordStrengthIndicator extends StatelessWidget {
  final String password;

  const PasswordStrengthIndicator({super.key, required this.password});

  @override
  Widget build(BuildContext context) {
    if (password.isEmpty) return const SizedBox.shrink();

    final res = PasswordStrengthResult.evaluate(password);
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Padding(
      padding: const EdgeInsets.only(top: 8.0, bottom: 4.0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                'Security: ${res.label}',
                style: TextStyle(
                  color: res.color,
                  fontWeight: FontWeight.bold,
                  fontSize: 12,
                ),
              ),
              Text(
                '${(res.score * 100).toInt()}%',
                style: TextStyle(
                  color: res.color,
                  fontWeight: FontWeight.bold,
                  fontSize: 12,
                ),
              ),
            ],
          ),
          const SizedBox(height: 6),
          // 4-Segmented Progress Bar
          Row(
            children: List.generate(4, (index) {
              final isFilled = (index + 1) * 0.25 <= res.score + 0.01;
              return Expanded(
                child: Container(
                  height: 4,
                  margin: EdgeInsets.only(right: index < 3 ? 4 : 0),
                  decoration: BoxDecoration(
                    color: isFilled
                        ? res.color
                        : (isDark ? const Color(0xFF283A5A) : const Color(0xFFE2E8F0)),
                    borderRadius: BorderRadius.circular(4),
                  ),
                ),
              );
            }),
          ),
          const SizedBox(height: 8),
          // Condition Chips
          Wrap(
            spacing: 6,
            runSpacing: 4,
            children: [
              _buildConditionChip('8+ Chars', res.hasMinLength, isDark),
              _buildConditionChip('Upper & Lower', res.hasUpperLower, isDark),
              _buildConditionChip('Number', res.hasDigit, isDark),
              _buildConditionChip('Symbol (!@#)', res.hasSpecialChar, isDark),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildConditionChip(String label, bool isMet, bool isDark) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
      decoration: BoxDecoration(
        color: isMet
            ? const Color(0xFF10B981).withValues(alpha: 0.15)
            : (isDark ? const Color(0xFF1B263B) : const Color(0xFFF1F5F9)),
        borderRadius: BorderRadius.circular(10),
        border: Border.all(
          color: isMet
              ? const Color(0xFF10B981)
              : (isDark ? const Color(0xFF415A77) : const Color(0xFFCBD5E1)),
          width: 0.8,
        ),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(
            isMet ? Icons.check : Icons.close,
            size: 11,
            color: isMet
                ? const Color(0xFF10B981)
                : (isDark ? Colors.white38 : const Color(0xFF94A3B8)),
          ),
          const SizedBox(width: 4),
          Text(
            label,
            style: TextStyle(
              fontSize: 10,
              color: isMet
                  ? const Color(0xFF10B981)
                  : (isDark ? Colors.white60 : const Color(0xFF64748B)),
              fontWeight: isMet ? FontWeight.bold : FontWeight.normal,
            ),
          ),
        ],
      ),
    );
  }
}
