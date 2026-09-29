import 'package:flutter/material.dart';

Widget buildSmileWinkView({
  required String userToken,
  required String winkUrl,
  required VoidCallback onAccountLinked,
  required VoidCallback onExit,
}) {
  return Container(
    color: const Color(0xFF0D1B2A),
    padding: const EdgeInsets.all(24),
    child: Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          const Icon(Icons.open_in_browser, color: Color(0xFF00E5FF), size: 48),
          const SizedBox(height: 16),
          const Text(
            'Smile Wink Account Linking',
            style: TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.bold),
          ),
          const SizedBox(height: 8),
          Text(
            winkUrl,
            textAlign: TextAlign.center,
            style: const TextStyle(color: Color(0xFF90E0EF), fontSize: 12),
          ),
          const SizedBox(height: 24),
          ElevatedButton.icon(
            onPressed: onAccountLinked,
            icon: const Icon(Icons.check_circle_outline),
            label: const Text('Complete Verification (Simulated)'),
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFF00E5FF),
              foregroundColor: const Color(0xFF0D1B2A),
            ),
          ),
        ],
      ),
    ),
  );
}
