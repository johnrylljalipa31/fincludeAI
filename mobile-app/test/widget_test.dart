import 'package:flutter_test/flutter_test.dart';
import 'package:finclude_app/main.dart';

void main() {
  testWidgets('App renders login screen with title and login button', (WidgetTester tester) async {
    await tester.pumpWidget(const FincludeApp());
    await tester.pumpAndSettle();

    expect(find.text('Finclude-AI'), findsWidgets);
    expect(find.text('Sign In to Dashboard'), findsOneWidget);
  });
}
