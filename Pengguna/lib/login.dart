import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:http/http.dart' as http;
import 'register.dart';
import 'navbar.dart';
import 'theme.dart';

class Login extends StatefulWidget {
  final VoidCallback? onLoginSuccess;
  final bool returnAfterLogin;

  const Login({
    super.key,
    this.onLoginSuccess,
    this.returnAfterLogin = false,
  });

  @override
  State<Login> createState() => _LoginState();
}

class _LoginState extends State<Login> {
  static const Color primaryPink = Color(0xFFE0245E);
  static const Color headerPink = Color(0xFFFF4B72);
  static const Color hintPink = Color(0xFFE9A1B7);

  final TextEditingController _emailController = TextEditingController();
  final TextEditingController _passwordController = TextEditingController();

  bool _obscurePassword = true;
  bool _isLoading = false;

  @override
  void dispose() {
    _emailController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  Future<void> _login() async {
    final emailOrHp = _emailController.text.trim();
    final password = _passwordController.text;

    if (emailOrHp.isEmpty || password.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Email/Nomor HP dan Password harus diisi!',
              style: GoogleFonts.plusJakartaSans()),
          backgroundColor: Colors.red,
        ),
      );
      return;
    }

    setState(() => _isLoading = true);

    try {
      final res = await http.post(
        Uri.parse('${AppTheme.apiUrl}/login'),
        headers: UserSession.authHeaders(json: true),
        body: jsonEncode({
          'email': emailOrHp,
          'password': password,
        }),
      );

      setState(() => _isLoading = false);
      final data = jsonDecode(res.body);

      if (res.statusCode == 200) {
        if (data['data'] != null) {
          await UserSession.save(Map<String, dynamic>.from(data['data']));
        }
        if (!mounted) return;
        if (widget.returnAfterLogin) {
          Navigator.pop(context, true);
          return;
        }
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Login berhasil!'),
            backgroundColor: Colors.green,
          ),
        );

        // Panggil callback sukses login jika ada
        if (widget.onLoginSuccess != null) {
          widget.onLoginSuccess!();
        }

        // Navigasi otomatis pindah ke halaman utama (NavbarPage)
        Future.delayed(const Duration(milliseconds: 600), () {
          if (mounted) {
            Navigator.pushReplacement(
              context,
              MaterialPageRoute(builder: (context) => const NavbarPage()),
            );
          }
        });
      } else {
        if (!mounted) return;
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(data['message'] ?? 'Login gagal!',
                style: GoogleFonts.plusJakartaSans()),
            backgroundColor: Colors.red,
          ),
        );
      }
    } catch (e) {
      setState(() => _isLoading = false);
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Kesalahan jaringan: $e',
              style: GoogleFonts.plusJakartaSans()),
          backgroundColor: Colors.red,
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.white,
      body: SafeArea(
        child: SingleChildScrollView(
          child: Column(
            children: [
              SizedBox(
                height: 175,
                child: Stack(
                  children: [
                    ClipPath(
                      clipper: HeaderClipper(),
                      child: Container(
                        height: 140,
                        width: double.infinity,
                        decoration: const BoxDecoration(
                          gradient: LinearGradient(
                            colors: [
                              Color(0xFFFF719B),
                              Color(0xFFFF3F78),
                            ],
                          ),
                        ),
                      ),
                    ),
                    Positioned(
                      bottom: 0,
                      left: 0,
                      right: 0,
                      child: Center(
                        child: Container(
                          width: 68,
                          height: 68,
                          decoration: BoxDecoration(
                            color: Colors.white,
                            shape: BoxShape.circle,
                            boxShadow: [
                              BoxShadow(
                                color: Colors.pink.withOpacity(0.2),
                                blurRadius: 10,
                                offset: const Offset(0, 5),
                              ),
                            ],
                          ),
                          child: const Icon(
                            Icons.handshake_outlined,
                            color: headerPink,
                            size: 36,
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
              Padding(
                padding: const EdgeInsets.symmetric(horizontal: 30),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Masuk ke Akun Anda',
                      style: GoogleFonts.plusJakartaSans(
                        fontSize: 19,
                        fontWeight: FontWeight.bold,
                        color: primaryPink,
                      ),
                    ),
                    const SizedBox(height: 5),
                    Text(
                      'Silakan login untuk melanjutkan',
                      style: GoogleFonts.plusJakartaSans(
                        fontSize: 11,
                        color: hintPink,
                      ),
                    ),
                    const SizedBox(height: 22),
                    Text(
                      'Email atau Nomor HP',
                      style: GoogleFonts.plusJakartaSans(
                        fontSize: 10,
                        color: primaryPink,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 6),
                    TextField(
                      controller: _emailController,
                      style: GoogleFonts.plusJakartaSans(fontSize: 12),
                      decoration: InputDecoration(
                        hintText: 'contoh@email.com',
                        hintStyle: GoogleFonts.plusJakartaSans(
                          fontSize: 9,
                          color: hintPink,
                        ),
                        prefixIcon: const Icon(
                          Icons.email_outlined,
                          color: primaryPink,
                          size: 18,
                        ),
                        enabledBorder: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(6),
                          borderSide: const BorderSide(color: headerPink),
                        ),
                        focusedBorder: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(6),
                          borderSide: const BorderSide(color: primaryPink),
                        ),
                      ),
                    ),
                    const SizedBox(height: 13),
                    Text(
                      'Password',
                      style: GoogleFonts.plusJakartaSans(
                        fontSize: 10,
                        color: primaryPink,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 6),
                    TextField(
                      controller: _passwordController,
                      obscureText: _obscurePassword,
                      style: GoogleFonts.plusJakartaSans(fontSize: 12),
                      decoration: InputDecoration(
                        hintText: 'Masukkan password',
                        hintStyle: GoogleFonts.plusJakartaSans(
                          fontSize: 9,
                          color: hintPink,
                        ),
                        prefixIcon: const Icon(
                          Icons.lock_outline,
                          color: primaryPink,
                          size: 18,
                        ),
                        suffixIcon: IconButton(
                          icon: Icon(
                            _obscurePassword
                                ? Icons.visibility_off_outlined
                                : Icons.visibility_outlined,
                            color: headerPink,
                            size: 17,
                          ),
                          onPressed: () {
                            setState(() {
                              _obscurePassword = !_obscurePassword;
                            });
                          },
                        ),
                        enabledBorder: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(6),
                          borderSide: const BorderSide(color: headerPink),
                        ),
                        focusedBorder: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(6),
                          borderSide: const BorderSide(color: primaryPink),
                        ),
                      ),
                    ),
                    const SizedBox(height: 15),
                    SizedBox(
                      width: double.infinity,
                      height: 43,
                      child: ElevatedButton(
                        onPressed: _isLoading ? null : _login,
                        style: ElevatedButton.styleFrom(
                          backgroundColor: primaryPink,
                          foregroundColor: Colors.white,
                          elevation: 0,
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(6),
                          ),
                        ),
                        child: _isLoading
                            ? const SizedBox(
                                width: 20,
                                height: 20,
                                child: CircularProgressIndicator(
                                    color: Colors.white, strokeWidth: 2),
                              )
                            : Text(
                                'Masuk',
                                style: GoogleFonts.plusJakartaSans(
                                  fontSize: 11,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                      ),
                    ),
                    const SizedBox(height: 5),
                    Center(
                      child: TextButton(
                        onPressed: () {},
                        child: Text(
                          'Lupa Password?',
                          style: GoogleFonts.plusJakartaSans(
                            fontSize: 9,
                            color: primaryPink,
                          ),
                        ),
                      ),
                    ),
                    Row(
                      children: [
                        const Expanded(
                            child: Divider(color: Color(0xFFF0C5D1))),
                        Padding(
                          padding: const EdgeInsets.symmetric(horizontal: 12),
                          child: Text(
                            'atau',
                            style: GoogleFonts.plusJakartaSans(
                                fontSize: 9, color: Colors.grey),
                          ),
                        ),
                        const Expanded(
                            child: Divider(color: Color(0xFFF0C5D1))),
                      ],
                    ),
                    const SizedBox(height: 10),
                    SizedBox(
                      width: double.infinity,
                      height: 43,
                      child: OutlinedButton(
                        onPressed: () {
                          Navigator.push(
                            context,
                            MaterialPageRoute(
                              builder: (context) => const Register(),
                            ),
                          );
                        },
                        style: OutlinedButton.styleFrom(
                          foregroundColor: primaryPink,
                          side: const BorderSide(color: headerPink),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(6),
                          ),
                        ),
                        child: Text(
                          'Daftar Akun',
                          style: GoogleFonts.plusJakartaSans(fontSize: 10),
                        ),
                      ),
                    ),
                    const SizedBox(height: 30),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class HeaderClipper extends CustomClipper<Path> {
  @override
  Path getClip(Size size) {
    final path = Path();
    path.lineTo(0, size.height - 25);
    path.quadraticBezierTo(
      size.width * 0.25,
      size.height + 15,
      size.width * 0.5,
      size.height - 5,
    );
    path.quadraticBezierTo(
      size.width * 0.75,
      size.height - 25,
      size.width,
      size.height - 5,
    );
    path.lineTo(size.width, 0);
    path.close();
    return path;
  }

  @override
  bool shouldReclip(CustomClipper<Path> oldClipper) => false;
}
