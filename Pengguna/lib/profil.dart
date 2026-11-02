import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:http/http.dart' as http;
import 'theme.dart';
import 'header.dart';
import 'login.dart';

class ProfilPage extends StatefulWidget {
  const ProfilPage({super.key});

  @override
  State<ProfilPage> createState() => _ProfilPageState();
}

class _ProfilPageState extends State<ProfilPage> {
  String get _noHp =>
      (UserSession.currentUser?['no_hp'] ?? '').toString().trim();

  void _tampilkanPesan(String pesan, {bool error = false}) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(
          pesan,
          style: GoogleFonts.plusJakartaSans(color: Colors.white),
        ),
        backgroundColor: error ? Colors.redAccent : Colors.green,
      ),
    );
  }

  Future<void> _editProfil() async {
    final id = UserSession.id;
    if (id == null) {
      _tampilkanPesan('Sesi akun tidak ditemukan. Silakan masuk kembali.',
          error: true);
      return;
    }

    final formKey = GlobalKey<FormState>();
    final namaController = TextEditingController(text: UserSession.name);
    final emailController = TextEditingController(text: UserSession.email);
    final noHpController = TextEditingController(text: _noHp);
    var sedangMenyimpan = false;

    await showDialog<void>(
      context: context,
      builder: (dialogContext) => StatefulBuilder(
        builder: (context, setDialogState) => AlertDialog(
          title: Text(
            'Edit Profil',
            style: GoogleFonts.plusJakartaSans(fontWeight: FontWeight.bold),
          ),
          content: Form(
            key: formKey,
            child: SingleChildScrollView(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  _formField(namaController, 'Nama', validator: (value) {
                    if (value == null || value.trim().isEmpty) {
                      return 'Nama wajib diisi';
                    }
                    return null;
                  }),
                  const SizedBox(height: 12),
                  _formField(
                    emailController,
                    'Email',
                    keyboardType: TextInputType.emailAddress,
                    validator: (value) {
                      if (value == null ||
                          !RegExp(r'^[^@\s]+@[^@\s]+\.[^@\s]+$')
                              .hasMatch(value.trim())) {
                        return 'Masukkan email yang valid';
                      }
                      return null;
                    },
                  ),
                  const SizedBox(height: 12),
                  _formField(
                    noHpController,
                    'Nomor HP / WhatsApp',
                    keyboardType: TextInputType.phone,
                    validator: (value) {
                      final nomor = (value ?? '').trim();
                      if (nomor.isNotEmpty &&
                          (nomor.length < 10 || nomor.length > 13)) {
                        return 'Nomor harus 10 sampai 13 digit';
                      }
                      return null;
                    },
                  ),
                ],
              ),
            ),
          ),
          actions: [
            TextButton(
              onPressed:
                  sedangMenyimpan ? null : () => Navigator.pop(dialogContext),
              child: const Text('Batal'),
            ),
            FilledButton(
              onPressed: sedangMenyimpan
                  ? null
                  : () async {
                      if (!formKey.currentState!.validate()) return;
                      setDialogState(() => sedangMenyimpan = true);
                      try {
                        final response = await http.put(
                          Uri.parse('${AppTheme.apiUrl}/pengguna/$id/profil'),
                          headers: UserSession.authHeaders(json: true),
                          body: jsonEncode({
                            'name': namaController.text.trim(),
                            'email': emailController.text.trim(),
                            'no_hp': noHpController.text.trim(),
                          }),
                        );
                        final data = jsonDecode(response.body);
                        if (response.statusCode == 200) {
                          await UserSession.save({
                            ...(UserSession.currentUser ?? <String, dynamic>{}),
                            ...Map<String, dynamic>.from(data['data']),
                          });
                          if (!mounted || !dialogContext.mounted) return;
                          setState(() {});
                          Navigator.pop(dialogContext);
                          _tampilkanPesan('Profil berhasil diperbarui.');
                        } else {
                          if (!dialogContext.mounted) return;
                          setDialogState(() => sedangMenyimpan = false);
                          _tampilkanPesan(
                            data['message'] ?? 'Profil gagal diperbarui.',
                            error: true,
                          );
                        }
                      } catch (_) {
                        if (!dialogContext.mounted) return;
                        setDialogState(() => sedangMenyimpan = false);
                        _tampilkanPesan(
                          'Tidak dapat terhubung ke server.',
                          error: true,
                        );
                      }
                    },
              child: sedangMenyimpan
                  ? const SizedBox(
                      width: 18,
                      height: 18,
                      child: CircularProgressIndicator(strokeWidth: 2),
                    )
                  : const Text('Simpan'),
            ),
          ],
        ),
      ),
    );
    namaController.dispose();
    emailController.dispose();
    noHpController.dispose();
  }

  Future<void> _gantiPassword() async {
    final id = UserSession.id;
    if (id == null) {
      _tampilkanPesan('Sesi akun tidak ditemukan. Silakan masuk kembali.',
          error: true);
      return;
    }

    final formKey = GlobalKey<FormState>();
    final passwordLama = TextEditingController();
    final passwordBaru = TextEditingController();
    final konfirmasi = TextEditingController();
    var sedangMenyimpan = false;
    var tampilkanLama = false;
    var tampilkanBaru = false;
    var tampilkanKonfirmasi = false;

    await showDialog<void>(
      context: context,
      builder: (dialogContext) => StatefulBuilder(
        builder: (context, setDialogState) => AlertDialog(
          title: Text(
            'Ganti Password',
            style: GoogleFonts.plusJakartaSans(fontWeight: FontWeight.bold),
          ),
          content: Form(
            key: formKey,
            child: SingleChildScrollView(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  _passwordField(
                    passwordLama,
                    'Password lama',
                    tampil: tampilkanLama,
                    onToggle: () =>
                        setDialogState(() => tampilkanLama = !tampilkanLama),
                  ),
                  const SizedBox(height: 12),
                  _passwordField(
                    passwordBaru,
                    'Password baru (minimal 8 karakter)',
                    tampil: tampilkanBaru,
                    onToggle: () =>
                        setDialogState(() => tampilkanBaru = !tampilkanBaru),
                    validator: (value) {
                      if ((value ?? '').length < 8) {
                        return 'Password minimal 8 karakter';
                      }
                      return null;
                    },
                  ),
                  const SizedBox(height: 12),
                  _passwordField(
                    konfirmasi,
                    'Konfirmasi password baru',
                    tampil: tampilkanKonfirmasi,
                    onToggle: () => setDialogState(
                        () => tampilkanKonfirmasi = !tampilkanKonfirmasi),
                    validator: (value) {
                      if (value != passwordBaru.text) {
                        return 'Konfirmasi password tidak cocok';
                      }
                      return null;
                    },
                  ),
                ],
              ),
            ),
          ),
          actions: [
            TextButton(
              onPressed:
                  sedangMenyimpan ? null : () => Navigator.pop(dialogContext),
              child: const Text('Batal'),
            ),
            FilledButton(
              onPressed: sedangMenyimpan
                  ? null
                  : () async {
                      if (!formKey.currentState!.validate()) return;
                      setDialogState(() => sedangMenyimpan = true);
                      try {
                        final response = await http.put(
                          Uri.parse('${AppTheme.apiUrl}/pengguna/$id/password'),
                          headers: UserSession.authHeaders(json: true),
                          body: jsonEncode({
                            'password_lama': passwordLama.text,
                            'password_baru': passwordBaru.text,
                            'password_baru_confirmation': konfirmasi.text,
                          }),
                        );
                        final data = jsonDecode(response.body);
                        if (response.statusCode == 200) {
                          if (!mounted || !dialogContext.mounted) return;
                          Navigator.pop(dialogContext);
                          _tampilkanPesan('Password berhasil diperbarui.');
                        } else {
                          if (!dialogContext.mounted) return;
                          setDialogState(() => sedangMenyimpan = false);
                          _tampilkanPesan(
                            data['message'] ?? 'Password gagal diperbarui.',
                            error: true,
                          );
                        }
                      } catch (_) {
                        if (!dialogContext.mounted) return;
                        setDialogState(() => sedangMenyimpan = false);
                        _tampilkanPesan(
                          'Tidak dapat terhubung ke server.',
                          error: true,
                        );
                      }
                    },
              child: sedangMenyimpan
                  ? const SizedBox(
                      width: 18,
                      height: 18,
                      child: CircularProgressIndicator(strokeWidth: 2),
                    )
                  : const Text('Simpan'),
            ),
          ],
        ),
      ),
    );
    passwordLama.dispose();
    passwordBaru.dispose();
    konfirmasi.dispose();
  }

  Widget _formField(
    TextEditingController controller,
    String label, {
    TextInputType? keyboardType,
    String? Function(String?)? validator,
  }) =>
      TextFormField(
        controller: controller,
        keyboardType: keyboardType,
        validator: validator,
        style: GoogleFonts.plusJakartaSans(fontSize: 13),
        decoration: InputDecoration(
          labelText: label,
          labelStyle: GoogleFonts.plusJakartaSans(fontSize: 12),
          border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
          contentPadding:
              const EdgeInsets.symmetric(horizontal: 12, vertical: 12),
        ),
      );

  Widget _passwordField(
    TextEditingController controller,
    String label, {
    required bool tampil,
    required VoidCallback onToggle,
    String? Function(String?)? validator,
  }) =>
      TextFormField(
        controller: controller,
        obscureText: !tampil,
        validator: validator ??
            (value) => (value ?? '').isEmpty ? 'Password wajib diisi' : null,
        style: GoogleFonts.plusJakartaSans(fontSize: 13),
        decoration: InputDecoration(
          labelText: label,
          labelStyle: GoogleFonts.plusJakartaSans(fontSize: 12),
          border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
          contentPadding:
              const EdgeInsets.symmetric(horizontal: 12, vertical: 12),
          suffixIcon: IconButton(
            onPressed: onToggle,
            tooltip: tampil ? 'Sembunyikan password' : 'Tampilkan password',
            icon: Icon(tampil ? Icons.visibility_off : Icons.visibility),
          ),
        ),
      );

  void _bukaPrivasi() {
    showModalBottomSheet<void>(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) => Container(
        height: MediaQuery.of(context).size.height * 0.65,
        padding: const EdgeInsets.fromLTRB(22, 20, 22, 28),
        decoration: const BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
        ),
        child: ListView(
          children: [
            Text(
              'Privasi & Keamanan',
              style: GoogleFonts.plusJakartaSans(
                fontSize: 17,
                fontWeight: FontWeight.bold,
                color: AppTheme.textDark,
              ),
            ),
            const SizedBox(height: 12),
            _privacyText(
              'Data laporan',
              'Informasi yang Anda kirim digunakan untuk membantu pencatatan dan penanganan laporan oleh petugas berwenang.',
            ),
            _privacyText(
              'Jaga keamanan akun',
              'Gunakan password yang kuat, jangan membagikan kode atau password, dan keluar dari akun pada perangkat bersama.',
            ),
            _privacyText(
              'Jaga kerahasiaan',
              'Hindari membagikan kronologi, identitas, atau bukti kepada pihak lain tanpa pertimbangan keamanan dan persetujuan korban.',
            ),
            _privacyText(
              'Butuh bantuan segera?',
              'Gunakan menu Bantuan Darurat atau hubungi SAPA 129 dan Polisi 110 jika keselamatan sedang terancam.',
            ),
          ],
        ),
      ),
    );
  }

  Widget _privacyText(String title, String description) => Padding(
        padding: const EdgeInsets.only(bottom: 14),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              title,
              style: GoogleFonts.plusJakartaSans(
                fontSize: 13,
                fontWeight: FontWeight.bold,
                color: AppTheme.primaryPink,
              ),
            ),
            const SizedBox(height: 4),
            Text(
              description,
              style: GoogleFonts.plusJakartaSans(
                fontSize: 12,
                height: 1.5,
                color: AppTheme.textGrey,
              ),
            ),
          ],
        ),
      );

  void _bukaTentang() {
    showAboutDialog(
      context: context,
      applicationName: 'Sahabat PPA',
      applicationVersion: '1.0.0',
      applicationIcon: const CircleAvatar(
        backgroundColor: AppTheme.primaryPink,
        child: Icon(Icons.favorite, color: Colors.white),
      ),
      children: [
        Text(
          'Sahabat PPA membantu pengguna memperoleh informasi, membuat laporan, dan memantau tindak lanjut terkait perlindungan perempuan dan anak.',
          style: GoogleFonts.plusJakartaSans(fontSize: 13, height: 1.5),
        ),
      ],
    );
  }

  void _keluarAkun() {
    showDialog<void>(
      context: context,
      builder: (dialogContext) => AlertDialog(
        title: Text(
          'Keluar dari akun?',
          style: GoogleFonts.plusJakartaSans(fontWeight: FontWeight.bold),
        ),
        content: Text(
          'Anda perlu masuk kembali untuk melihat profil dan laporan.',
          style: GoogleFonts.plusJakartaSans(fontSize: 13),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(dialogContext),
            child: const Text('Batal'),
          ),
          FilledButton(
            onPressed: () async {
              try {
                await UserSession.logout();
              } catch (error) {
                debugPrint('Logout gagal: $error');
                await UserSession.clear();
              }
              if (!mounted || !dialogContext.mounted) return;
              Navigator.pop(dialogContext);
              Navigator.pushAndRemoveUntil(
                context,
                MaterialPageRoute(builder: (_) => Login(onLoginSuccess: () {})),
                (route) => false,
              );
            },
            child: const Text('Keluar'),
          ),
        ],
      ),
    );
  }

  Widget _menuItem({
    required IconData icon,
    required String title,
    required String subtitle,
    required VoidCallback onTap,
    Color color = AppTheme.primaryPink,
  }) =>
      Material(
        color: Colors.transparent,
        child: ListTile(
          leading: Icon(icon, color: color),
          title: Text(
            title,
            style: GoogleFonts.plusJakartaSans(
              fontSize: 13,
              fontWeight: FontWeight.w600,
            ),
          ),
          subtitle: Text(
            subtitle,
            style: GoogleFonts.plusJakartaSans(fontSize: 11),
          ),
          trailing:
              const Icon(Icons.chevron_right, size: 20, color: Colors.grey),
          onTap: onTap,
        ),
      );

  @override
  Widget build(BuildContext context) {
    return BaseLayout(
      showBackButton: false,
      child: ListView(
        padding: const EdgeInsets.fromLTRB(20, 20, 20, 28),
        children: [
          Container(
            width: double.infinity,
            padding: const EdgeInsets.all(24),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(18),
              border: Border.all(color: const Color(0xFFF0F0F2)),
            ),
            child: Column(
              children: [
                const CircleAvatar(
                  radius: 42,
                  backgroundColor: Color(0xFFFFEEF2),
                  child:
                      Icon(Icons.person, size: 44, color: AppTheme.primaryPink),
                ),
                const SizedBox(height: 12),
                Text(
                  UserSession.name,
                  style: GoogleFonts.plusJakartaSans(
                    fontSize: 18,
                    fontWeight: FontWeight.bold,
                    color: AppTheme.textDark,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  UserSession.email,
                  style: GoogleFonts.plusJakartaSans(
                    fontSize: 12,
                    color: AppTheme.textGrey,
                  ),
                ),
                if (_noHp.isNotEmpty) ...[
                  const SizedBox(height: 3),
                  Text(
                    _noHp,
                    style: GoogleFonts.plusJakartaSans(
                      fontSize: 11,
                      color: AppTheme.textGrey,
                    ),
                  ),
                ],
                const SizedBox(height: 14),
                OutlinedButton.icon(
                  onPressed: _editProfil,
                  icon: const Icon(Icons.edit_outlined, size: 16),
                  label: const Text('Edit Profil'),
                ),
              ],
            ),
          ),
          const SizedBox(height: 16),
          Container(
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(16),
              border: Border.all(color: const Color(0xFFF0F0F2)),
            ),
            child: Column(
              children: [
                _menuItem(
                  icon: Icons.lock_outline,
                  title: 'Ganti Password',
                  subtitle: 'Perbarui keamanan akun Anda',
                  onTap: _gantiPassword,
                ),
                const Divider(height: 1, indent: 56),
                _menuItem(
                  icon: Icons.shield_outlined,
                  title: 'Kebijakan Privasi & Keamanan',
                  subtitle: 'Informasi penggunaan dan perlindungan data',
                  onTap: _bukaPrivasi,
                ),
                const Divider(height: 1, indent: 56),
                _menuItem(
                  icon: Icons.info_outline,
                  title: 'Tentang Aplikasi SAPA',
                  subtitle: 'Tujuan dan versi aplikasi',
                  onTap: _bukaTentang,
                ),
                const Divider(height: 1, indent: 56),
                _menuItem(
                  icon: Icons.logout,
                  title: 'Keluar dari Akun',
                  subtitle: 'Akhiri sesi pada perangkat ini',
                  color: Colors.redAccent,
                  onTap: _keluarAkun,
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
