import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';

class ThemeProvider with ChangeNotifier {
  static const String _themePrefKey = 'finclude_theme_mode';
  ThemeMode _themeMode = ThemeMode.dark;

  ThemeMode get themeMode => _themeMode;
  bool get isDarkMode => _themeMode == ThemeMode.dark;

  ThemeProvider() {
    _loadThemePreference();
  }

  void _loadThemePreference() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final isDark = prefs.getBool(_themePrefKey);
      if (isDark != null) {
        _themeMode = isDark ? ThemeMode.dark : ThemeMode.light;
        notifyListeners();
      }
    } catch (_) {}
  }

  void toggleTheme() async {
    _themeMode = _themeMode == ThemeMode.dark ? ThemeMode.light : ThemeMode.dark;
    notifyListeners();
    try {
      final prefs = await SharedPreferences.getInstance();
      await prefs.setBool(_themePrefKey, _themeMode == ThemeMode.dark);
    } catch (_) {}
  }

  // Dark Theme Palette
  static ThemeData get darkTheme {
    const bg = Color(0xFF0B1325);
    const cardBg = Color(0xFF152238);
    const primary = Color(0xFF00E5FF);
    const secondary = Color(0xFF90E0EF);

    return ThemeData(
      brightness: Brightness.dark,
      scaffoldBackgroundColor: bg,
      primaryColor: primary,
      cardColor: cardBg,
      colorScheme: const ColorScheme.dark(
        primary: primary,
        secondary: secondary,
        surface: cardBg,
      ),
      appBarTheme: const AppBarTheme(
        backgroundColor: bg,
        foregroundColor: Colors.white,
        elevation: 0,
        centerTitle: false,
      ),
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: cardBg,
        labelStyle: const TextStyle(color: secondary),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(14),
          borderSide: const BorderSide(color: Color(0xFF283A5A)),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(14),
          borderSide: const BorderSide(color: Color(0xFF283A5A)),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(14),
          borderSide: const BorderSide(color: primary, width: 2),
        ),
      ),
      elevatedButtonTheme: ElevatedButtonThemeData(
        style: ElevatedButton.styleFrom(
          backgroundColor: primary,
          foregroundColor: const Color(0xFF0B1325),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
          textStyle: const TextStyle(fontWeight: FontWeight.bold),
        ),
      ),
      fontFamily: 'Roboto',
    );
  }

  // Light Theme Palette
  static ThemeData get lightTheme {
    const bg = Color(0xFFF6F8FC);
    const cardBg = Color(0xFFFFFFFF);
    const primary = Color(0xFF0284C7); // Rich Electric Sky Blue
    const secondary = Color(0xFF475569);

    return ThemeData(
      brightness: Brightness.light,
      scaffoldBackgroundColor: bg,
      primaryColor: primary,
      cardColor: cardBg,
      colorScheme: const ColorScheme.light(
        primary: primary,
        secondary: secondary,
        surface: cardBg,
      ),
      appBarTheme: const AppBarTheme(
        backgroundColor: bg,
        foregroundColor: Color(0xFF0F172A),
        elevation: 0,
        centerTitle: false,
      ),
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: cardBg,
        labelStyle: const TextStyle(color: secondary),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(14),
          borderSide: const BorderSide(color: Color(0xFFE2E8F0)),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(14),
          borderSide: const BorderSide(color: Color(0xFFE2E8F0)),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(14),
          borderSide: const BorderSide(color: primary, width: 2),
        ),
      ),
      elevatedButtonTheme: ElevatedButtonThemeData(
        style: ElevatedButton.styleFrom(
          backgroundColor: primary,
          foregroundColor: Colors.white,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
          textStyle: const TextStyle(fontWeight: FontWeight.bold),
        ),
      ),
      fontFamily: 'Roboto',
    );
  }
}
