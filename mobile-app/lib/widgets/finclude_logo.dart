import 'package:flutter/material.dart';

class FincludeLogo extends StatelessWidget {
  final double size;
  final bool showText;
  final bool vertical;
  final double? fontSize;
  final VoidCallback? onTap;

  const FincludeLogo({
    super.key,
    this.size = 64,
    this.showText = false,
    this.vertical = true,
    this.fontSize,
    this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final textColor = isDark ? Colors.white : const Color(0xFF0F172A);

    final logoWidget = Container(
      width: size,
      height: size,
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(size * 0.22),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF00E5FF).withValues(alpha: isDark ? 0.35 : 0.2),
            blurRadius: size * 0.25,
            spreadRadius: size * 0.03,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: ClipRRect(
        borderRadius: BorderRadius.circular(size * 0.22),
        child: Image.asset(
          'assets/images/finclude_logo.png',
          width: size,
          height: size,
          fit: BoxFit.cover,
          errorBuilder: (context, error, stackTrace) {
            // Fallback decorative gradient icon if asset loading fails in test
            return Container(
              color: const Color(0xFF0D1B2A),
              child: Center(
                child: Icon(
                  Icons.shield_rounded,
                  color: const Color(0xFF00E5FF),
                  size: size * 0.6,
                ),
              ),
            );
          },
        ),
      ),
    );

    if (!showText) {
      return GestureDetector(onTap: onTap, child: logoWidget);
    }

    final textWidget = Text(
      'Finclude-AI',
      style: TextStyle(
        fontSize: fontSize ?? (size * 0.4),
        fontWeight: FontWeight.bold,
        letterSpacing: 1.1,
        color: textColor,
      ),
    );

    final content = vertical
        ? Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              logoWidget,
              SizedBox(height: size * 0.2),
              textWidget,
            ],
          )
        : Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              logoWidget,
              SizedBox(width: size * 0.2),
              textWidget,
            ],
          );

    return GestureDetector(onTap: onTap, child: content);
  }
}
