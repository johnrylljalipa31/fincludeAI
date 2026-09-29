import 'dart:js_interop';
import 'dart:ui_web' as ui_web;
import 'package:flutter/material.dart';
import 'package:web/web.dart' as web;

class SmileWinkWebView extends StatefulWidget {
  final String userToken;
  final String winkUrl;
  final VoidCallback onAccountLinked;
  final VoidCallback onExit;

  const SmileWinkWebView({
    super.key,
    required this.userToken,
    required this.winkUrl,
    required this.onAccountLinked,
    required this.onExit,
  });

  @override
  State<SmileWinkWebView> createState() => _SmileWinkWebViewState();
}

class _SmileWinkWebViewState extends State<SmileWinkWebView> {
  late final String _viewType;
  JSFunction? _messageListener;

  @override
  void initState() {
    super.initState();
    _viewType = 'smile-wink-iframe-${DateTime.now().microsecondsSinceEpoch}';

    // Register platform view factory to host Smile Wink inside a sandboxed iframe
    ui_web.platformViewRegistry.registerViewFactory(
      _viewType,
      (int viewId) {
        final iframe = web.HTMLIFrameElement()
          ..src = widget.winkUrl
          ..style.border = 'none'
          ..style.width = '100%'
          ..style.height = '100%'
          ..setAttribute('allow', 'camera; microphone; geolocation; payment')
          ..setAttribute('sandbox', 'allow-scripts allow-same-origin allow-forms allow-popups');
        return iframe;
      },
    );

    _listenToWinkMessages();
  }

  void _listenToWinkMessages() {
    final callback = ((web.MessageEvent event) {
      final origin = event.origin;
      final dataStr = event.data.toString().toLowerCase();

      // Check if event originates from Smile API Wink widget
      if (origin.contains('smileapi.io') || dataStr.contains('smile') || dataStr.contains('account')) {
        if (dataStr.contains('success') || dataStr.contains('connected') || dataStr.contains('linked')) {
          widget.onAccountLinked();
        } else if (dataStr.contains('exit') || dataStr.contains('close')) {
          widget.onExit();
        }
      }
    }).toJS;

    _messageListener = callback;
    web.window.addEventListener('message', callback);
  }

  @override
  void dispose() {
    if (_messageListener != null) {
      web.window.removeEventListener('message', _messageListener!);
    }
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return HtmlElementView(viewType: _viewType);
  }
}

Widget buildSmileWinkView({
  required String userToken,
  required String winkUrl,
  required VoidCallback onAccountLinked,
  required VoidCallback onExit,
}) {
  return SmileWinkWebView(
    userToken: userToken,
    winkUrl: winkUrl,
    onAccountLinked: onAccountLinked,
    onExit: onExit,
  );
}
