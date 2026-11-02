import 'package:flutter_test/flutter_test.dart';
import 'package:ppa_flutter/login.dart';
import 'package:ppa_flutter/main.dart';
import 'package:ppa_flutter/theme.dart';
import 'package:shared_preferences/shared_preferences.dart';

void main() {
  testWidgets('app opens on the login screen without a session',
      (tester) async {
    UserSession.currentUser = null;
    await tester.pumpWidget(const MyApp());

    expect(find.byType(Login), findsOneWidget);
  });

  test('saved authentication session can be restored', () async {
    SharedPreferences.setMockInitialValues({});
    await UserSession.save(
        {'id': 42, 'name': 'Pelapor', 'token': 'test-token'});

    UserSession.currentUser = null;
    await UserSession.restore();

    expect(UserSession.id, 42);
    expect(UserSession.authHeaders()['Authorization'], 'Bearer test-token');
    await UserSession.clear();
  });
}
