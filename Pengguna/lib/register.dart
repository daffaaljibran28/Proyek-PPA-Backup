import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:http/http.dart' as http;
import 'theme.dart';

class Register extends StatefulWidget {
  const Register({super.key});

  @override
  State<Register> createState() => _RegisterState();
}

class _RegisterState extends State<Register> {
  static const Color primaryPink = Color(0xFFE0245E);
  static const Color headerPink = Color(0xFFFF4B72);
  static const Color hintPink = Color(0xFFE9A1B7);

  final TextEditingController _namaController = TextEditingController();
  final TextEditingController _nikController = TextEditingController();
  final TextEditingController _tanggalController = TextEditingController();
  final TextEditingController _alamatController = TextEditingController();
  final TextEditingController _emailController = TextEditingController();
  final TextEditingController _noHpController = TextEditingController();
  final TextEditingController _passwordController = TextEditingController();
  final TextEditingController _konfirmasiPasswordController =
      TextEditingController();

  bool _obscurePassword = true;
  bool _obscureKonfirmasi = true;
  bool _isLoading = false;

  // Error messages untuk setiap field
  String? _errNama;
  String? _errNik;
  String? _errTanggal;
  String? _errAlamat;
  String? _errEmail;
  String? _errNoHp;
  String? _errPassword;
  String? _errKonfirmasi;

  @override
  void dispose() {
    _namaController.dispose();
    _nikController.dispose();
    _tanggalController.dispose();
    _alamatController.dispose();
    _emailController.dispose();
    _noHpController.dispose();
    _passwordController.dispose();
    _konfirmasiPasswordController.dispose();
    super.dispose();
  }

  Future<void> _pilihTanggal() async {
    final DateTime? tanggal = await showDatePicker(
      context: context,
      initialDate: DateTime(2000),
      firstDate: DateTime(1940),
      lastDate: DateTime.now(),
    );

    if (tanggal != null) {
      setState(() {
        _tanggalController.text = '${tanggal.day.toString().padLeft(2, '0')}/'
            '${tanggal.month.toString().padLeft(2, '0')}/'
            '${tanggal.year}';
        _errTanggal = null;
      });
    }
  }

  // Validasi semua field, kembalikan true jika valid
  bool _validasi() {
    final nama = _namaController.text.trim();
    final nik = _nikController.text.trim();
    final tanggal = _tanggalController.text.trim();
    final alamat = _alamatController.text.trim();
    final email = _emailController.text.trim();
    final noHp = _noHpController.text.trim();
    final password = _passwordController.text;
    final konfirmasi = _konfirmasiPasswordController.text;

    bool valid = true;

    setState(() {
      // Nama
      if (nama.isEmpty) {
        _errNama = 'Nama lengkap wajib diisi.';
        valid = false;
      } else {
        _errNama = null;
      }

      // NIK
      if (nik.isEmpty) {
        _errNik = 'NIK wajib diisi.';
        valid = false;
      } else if (RegExp(r'[^0-9]').hasMatch(nik)) {
        _errNik = 'NIK tidak boleh menggunakan huruf, spasi, atau simbol.';
        valid = false;
      } else if (nik.length != 16) {
        _errNik =
            'NIK harus tepat 16 digit angka (saat ini: ${nik.length} digit).';
        valid = false;
      } else {
        _errNik = null;
      }

      // Tanggal Lahir
      if (tanggal.isEmpty) {
        _errTanggal = 'Tanggal lahir wajib dipilih.';
        valid = false;
      } else {
        _errTanggal = null;
      }

      // Alamat
      if (alamat.isEmpty) {
        _errAlamat = 'Alamat wajib diisi.';
        valid = false;
      } else {
        _errAlamat = null;
      }

      // Email
      if (email.isEmpty) {
        _errEmail = 'Email wajib diisi.';
        valid = false;
      } else if (!email.endsWith('@gmail.com') ||
          email.length <= '@gmail.com'.length) {
        _errEmail =
            'Email harus menggunakan @gmail.com (contoh: nama@gmail.com).';
        valid = false;
      } else {
        _errEmail = null;
      }

      // No HP
      if (noHp.isEmpty) {
        _errNoHp = 'Nomor HP wajib diisi.';
        valid = false;
      } else if (RegExp(r'[^0-9]').hasMatch(noHp)) {
        _errNoHp =
            'Nomor HP tidak boleh menggunakan huruf, spasi, atau simbol.';
        valid = false;
      } else if (!noHp.startsWith('08')) {
        _errNoHp = 'Nomor HP harus diawali dengan "08" (contoh: 081234567890).';
        valid = false;
      } else if (noHp.length < 10 || noHp.length > 13) {
        _errNoHp =
            'Nomor HP harus 10–13 digit angka (saat ini: ${noHp.length} digit).';
        valid = false;
      } else {
        _errNoHp = null;
      }

      // Password
      if (password.isEmpty) {
        _errPassword = 'Password wajib diisi.';
        valid = false;
      } else if (password.length < 8) {
        _errPassword =
            'Password minimal 8 karakter (saat ini: ${password.length} karakter).';
        valid = false;
      } else if (!RegExp(r'[A-Z]').hasMatch(password)) {
        _errPassword = 'Password harus mengandung minimal 1 huruf besar (A-Z).';
        valid = false;
      } else if (!RegExp(r'[a-z]').hasMatch(password)) {
        _errPassword = 'Password harus mengandung minimal 1 huruf kecil (a-z).';
        valid = false;
      } else if (!RegExp(r'[0-9]').hasMatch(password)) {
        _errPassword = 'Password harus mengandung minimal 1 angka (0-9).';
        valid = false;
      } else if (!RegExp(r'[!@#$%^&*]').hasMatch(password)) {
        _errPassword =
            'Password harus mengandung minimal 1 karakter khusus (!@#%^&*).';
        valid = false;
      } else {
        _errPassword = null;
      }

      // Konfirmasi Password
      if (konfirmasi.isEmpty) {
        _errKonfirmasi = 'Konfirmasi password wajib diisi.';
        valid = false;
      } else if (password != konfirmasi) {
        _errKonfirmasi = 'Konfirmasi password tidak cocok.';
        valid = false;
      } else {
        _errKonfirmasi = null;
      }
    });

    return valid;
  }

  Future<void> _daftar() async {
    if (!_validasi()) {
      final errorPesan = _errNama ??
          _errNik ??
          _errTanggal ??
          _errAlamat ??
          _errEmail ??
          _errNoHp ??
          _errPassword ??
          _errKonfirmasi ??
          'Mohon periksa kembali data pendaftaran Anda.';
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            errorPesan,
            style: GoogleFonts.plusJakartaSans(),
          ),
          backgroundColor: Colors.red,
          duration: const Duration(seconds: 3),
        ),
      );
      return;
    }

    final nama = _namaController.text.trim();
    final nik = _nikController.text.trim();
    final tanggal = _tanggalController.text.trim();
    final alamat = _alamatController.text.trim();
    final email = _emailController.text.trim();
    final noHp = _noHpController.text.trim();
    final password = _passwordController.text;

    setState(() => _isLoading = true);

    try {
      final res = await http.post(
        Uri.parse('${AppTheme.apiUrl}/register'),
        headers: UserSession.authHeaders(json: true),
        body: jsonEncode({
          'name': nama,
          'nik': nik,
          'tanggal_lahir': tanggal,
          'alamat': alamat,
          'email': email,
          'no_hp': noHp,
          'password': password,
        }),
      );

      setState(() => _isLoading = false);

      dynamic data;
      try {
        data = jsonDecode(res.body);
      } catch (_) {
        data = null;
      }

      if (res.statusCode == 200 || res.statusCode == 201) {
        if (!mounted) return;
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Registrasi berhasil! Silakan login.',
                style: GoogleFonts.plusJakartaSans()),
            backgroundColor: Colors.green,
          ),
        );
        Future.delayed(const Duration(milliseconds: 800), () {
          if (mounted) Navigator.pop(context);
        });
      } else {
        if (!mounted) return;
        String pesanPeringatan = '';
        if (data != null &&
            data is Map &&
            data.containsKey('errors') &&
            data['errors'] is Map) {
          final errs = data['errors'] as Map;
          setState(() {
            if (errs.containsKey('nik') &&
                errs['nik'] is List &&
                (errs['nik'] as List).isNotEmpty) {
              _errNik = errs['nik'].first.toString();
              pesanPeringatan += '${_errNik!}\n';
            }
            if (errs.containsKey('email') &&
                errs['email'] is List &&
                (errs['email'] as List).isNotEmpty) {
              _errEmail = errs['email'].first.toString();
              pesanPeringatan += '${_errEmail!}\n';
            }
            if (errs.containsKey('no_hp') &&
                errs['no_hp'] is List &&
                (errs['no_hp'] as List).isNotEmpty) {
              _errNoHp = errs['no_hp'].first.toString();
              pesanPeringatan += '${_errNoHp!}\n';
            }
            if (errs.containsKey('password') &&
                errs['password'] is List &&
                (errs['password'] as List).isNotEmpty) {
              _errPassword = errs['password'].first.toString();
              pesanPeringatan += '${_errPassword!}\n';
            }
          });
        } else if (data != null && data is Map && data.containsKey('message')) {
          pesanPeringatan = data['message'].toString();
          setState(() => _errEmail = pesanPeringatan);
        } else {
          pesanPeringatan = 'Registrasi gagal (Status ${res.statusCode}).';
          setState(() => _errEmail = pesanPeringatan);
        }

        if (pesanPeringatan.isNotEmpty) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text(
                pesanPeringatan.trim(),
                style: GoogleFonts.plusJakartaSans(),
              ),
              backgroundColor: Colors.red,
              duration: const Duration(seconds: 4),
            ),
          );
        }
      }
    } catch (e) {
      debugPrint('Error Register: $e');
      setState(() => _isLoading = false);
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            'Gagal terhubung ke server backend. Pastikan server Laravel sedang berjalan (php artisan serve).',
            style: GoogleFonts.plusJakartaSans(),
          ),
          backgroundColor: Colors.red,
          duration: const Duration(seconds: 4),
        ),
      );
    }
  }

  Widget _buildField({
    required String label,
    required String hint,
    required IconData icon,
    required TextEditingController controller,
    String? errorText,
    VoidCallback? onChanged,
    TextInputType? keyboardType,
    bool obscureText = false,
    Widget? suffixIcon,
    VoidCallback? onTap,
    int maxLines = 1,
  }) {
    final hasError = errorText != null && errorText.isNotEmpty;
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          label,
          style: GoogleFonts.plusJakartaSans(
            fontSize: 10,
            color: hasError ? Colors.red[700] : primaryPink,
            fontWeight: FontWeight.bold,
          ),
        ),
        const SizedBox(height: 6),
        TextField(
          controller: controller,
          keyboardType: keyboardType,
          obscureText: obscureText,
          onTap: onTap,
          readOnly: onTap != null,
          maxLines: maxLines,
          style: GoogleFonts.plusJakartaSans(fontSize: 12),
          onChanged: (_) {
            if (hasError) {
              setState(() {
                if (onChanged != null) onChanged();
              });
            }
          },
          decoration: InputDecoration(
            hintText: hint,
            hintStyle: GoogleFonts.plusJakartaSans(
              fontSize: 9,
              color: hintPink,
            ),
            prefixIcon: Icon(
              icon,
              color: hasError ? Colors.red[400] : primaryPink,
              size: 18,
            ),
            suffixIcon: suffixIcon,
            contentPadding: const EdgeInsets.symmetric(
              horizontal: 12,
              vertical: 13,
            ),
            enabledBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(6),
              borderSide: BorderSide(
                color: hasError ? Colors.red : headerPink,
                width: hasError ? 1.5 : 1.0,
              ),
            ),
            focusedBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(6),
              borderSide: BorderSide(
                color: hasError ? Colors.red : primaryPink,
                width: 1.5,
              ),
            ),
          ),
        ),
        if (hasError) ...[
          const SizedBox(height: 4),
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Icon(Icons.error_outline, color: Colors.red, size: 13),
              const SizedBox(width: 4),
              Expanded(
                child: Text(
                  errorText,
                  style: GoogleFonts.plusJakartaSans(
                    fontSize: 9.5,
                    color: Colors.red[700],
                    fontWeight: FontWeight.w500,
                  ),
                ),
              ),
            ],
          ),
        ],
      ],
    );
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
                      clipper: RegisterHeaderClipper(),
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
                      left: 15,
                      top: 18,
                      child: Container(
                        width: 42,
                        height: 42,
                        decoration: BoxDecoration(
                          color: Colors.white.withValues(alpha: 0.24),
                          shape: BoxShape.circle,
                          border: Border.all(
                            color: Colors.white.withValues(alpha: 0.4),
                          ),
                          boxShadow: [
                            BoxShadow(
                              color: Colors.black.withValues(alpha: 0.08),
                              blurRadius: 10,
                              offset: const Offset(0, 3),
                            ),
                          ],
                        ),
                        child: IconButton(
                          padding: EdgeInsets.zero,
                          tooltip: 'Kembali',
                          onPressed: () => Navigator.maybePop(context),
                          icon: const Icon(
                            Icons.arrow_back_rounded,
                            color: Colors.white,
                            size: 20,
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
                                color: Colors.pink.withValues(alpha: 0.2),
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
                padding: const EdgeInsets.symmetric(horizontal: 25),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Daftar Akun',
                      style: GoogleFonts.plusJakartaSans(
                        fontSize: 19,
                        fontWeight: FontWeight.bold,
                        color: primaryPink,
                      ),
                    ),
                    const SizedBox(height: 5),
                    Text(
                      'Buat akun baru untuk melaporkan kasus dan\nmemantau status laporan.',
                      style: GoogleFonts.plusJakartaSans(
                        fontSize: 11,
                        color: hintPink,
                        height: 1.4,
                      ),
                    ),
                    const SizedBox(height: 18),
                    _buildField(
                      label: 'Nama Lengkap',
                      hint: 'Masukkan nama lengkap',
                      icon: Icons.person_outline,
                      controller: _namaController,
                      errorText: _errNama,
                      onChanged: () => _errNama = null,
                    ),
                    const SizedBox(height: 11),
                    _buildField(
                      label: 'NIK (16 Digit Angka, Tanpa Huruf/Simbol/Spasi)',
                      hint: 'Contoh: 3271234567890001',
                      icon: Icons.credit_card_outlined,
                      controller: _nikController,
                      keyboardType: TextInputType.number,
                      errorText: _errNik,
                      onChanged: () => _errNik = null,
                    ),
                    const SizedBox(height: 11),
                    _buildField(
                      label: 'Tanggal Lahir',
                      hint: 'Pilih tanggal lahir',
                      icon: Icons.calendar_month_outlined,
                      controller: _tanggalController,
                      onTap: _pilihTanggal,
                      errorText: _errTanggal,
                      suffixIcon: const Icon(
                        Icons.calendar_today_outlined,
                        color: primaryPink,
                        size: 17,
                      ),
                    ),
                    const SizedBox(height: 11),
                    _buildField(
                      label: 'Alamat',
                      hint: 'Masukkan alamat lengkap',
                      icon: Icons.location_on_outlined,
                      controller: _alamatController,
                      errorText: _errAlamat,
                      onChanged: () => _errAlamat = null,
                    ),
                    const SizedBox(height: 11),
                    _buildField(
                      label: 'Email (Wajib @gmail.com)',
                      hint: 'contoh@gmail.com',
                      icon: Icons.email_outlined,
                      controller: _emailController,
                      keyboardType: TextInputType.emailAddress,
                      errorText: _errEmail,
                      onChanged: () => _errEmail = null,
                    ),
                    const SizedBox(height: 11),
                    _buildField(
                      label: 'Nomor HP (Awali "08", Tanpa Huruf/Simbol/Spasi)',
                      hint: 'Contoh: 081234567890',
                      icon: Icons.phone_outlined,
                      controller: _noHpController,
                      keyboardType: TextInputType.phone,
                      errorText: _errNoHp,
                      onChanged: () => _errNoHp = null,
                    ),
                    const SizedBox(height: 11),
                    _buildField(
                      label: 'Password',
                      hint: 'Masukkan password',
                      icon: Icons.lock_outline,
                      controller: _passwordController,
                      obscureText: _obscurePassword,
                      errorText: _errPassword,
                      onChanged: () => _errPassword = null,
                      suffixIcon: IconButton(
                        icon: Icon(
                          _obscurePassword
                              ? Icons.visibility_off_outlined
                              : Icons.visibility_outlined,
                          color: headerPink,
                          size: 17,
                        ),
                        onPressed: () => setState(
                            () => _obscurePassword = !_obscurePassword),
                      ),
                    ),
                    const SizedBox(height: 11),
                    _buildField(
                      label: 'Konfirmasi Password',
                      hint: 'Ulangi password',
                      icon: Icons.lock_outline,
                      controller: _konfirmasiPasswordController,
                      obscureText: _obscureKonfirmasi,
                      errorText: _errKonfirmasi,
                      onChanged: () => _errKonfirmasi = null,
                      suffixIcon: IconButton(
                        icon: Icon(
                          _obscureKonfirmasi
                              ? Icons.visibility_off_outlined
                              : Icons.visibility_outlined,
                          color: headerPink,
                          size: 17,
                        ),
                        onPressed: () => setState(
                            () => _obscureKonfirmasi = !_obscureKonfirmasi),
                      ),
                    ),
                    const SizedBox(height: 13),
                    SizedBox(
                      width: double.infinity,
                      height: 43,
                      child: ElevatedButton(
                        onPressed: _isLoading ? null : _daftar,
                        style: ElevatedButton.styleFrom(
                          backgroundColor: const Color(0xFFC92C5B),
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
                                'Daftar',
                                style: GoogleFonts.plusJakartaSans(
                                  fontSize: 11,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                      ),
                    ),
                    const SizedBox(height: 8),
                    Center(
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Text(
                            'Sudah punya akun? ',
                            style: GoogleFonts.plusJakartaSans(
                                fontSize: 9, color: hintPink),
                          ),
                          GestureDetector(
                            onTap: () => Navigator.pop(context),
                            child: Text(
                              'Login',
                              style: GoogleFonts.plusJakartaSans(
                                fontSize: 9,
                                color: primaryPink,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 18),
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

class RegisterHeaderClipper extends CustomClipper<Path> {
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
