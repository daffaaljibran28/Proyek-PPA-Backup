import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'theme.dart';
import 'navbar.dart';
import 'login.dart'; // Import halaman login

Future<void> main() async {
  WidgetsFlutterBinding.ensureInitialized();
  await UserSession.restore();
  runApp(const MyApp());
}

class MyApp extends StatelessWidget {
  const MyApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Sahabat PPA',
      debugShowCheckedModeBanner: false,
      theme: ThemeData(
        scaffoldBackgroundColor: AppTheme.bgLight,
        textTheme:
            GoogleFonts.plusJakartaSansTextTheme(Theme.of(context).textTheme),
      ),
      routes: {
        '/home': (context) => const NavbarPage(),
        '/login': (context) => const Login(),
      },
      // Aplikasi terbuka langsung di Halaman Login saat awal dibuka
      home:
          UserSession.currentUser == null ? const Login() : const NavbarPage(),
    );
  }
}
