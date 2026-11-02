import 'dart:convert';

import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

class AppTheme {
  static String get apiUrl {
    const configuredUrl = String.fromEnvironment('API_BASE_URL');
    if (configuredUrl.isNotEmpty) {
      return configuredUrl.replaceFirst(RegExp(r'/+$'), '');
    }

    if (kIsWeb) {
      final host = Uri.base.host.isEmpty ? 'localhost' : Uri.base.host;
      return 'http://$host:8000/api';
    }

    return switch (defaultTargetPlatform) {
      TargetPlatform.android => 'http://10.0.2.2:8000/api',
      TargetPlatform.iOS => 'http://127.0.0.1:8000/api',
      _ => 'http://127.0.0.1:8000/api',
    };
  }

  static const Color primaryPink = Color(0xFFE0245E);
  static const Color headerPink = Color(0xFFFF4B72);
  static const Color bgLight = Color(0xFFFBFBFD);
  static const Color cardBg = Colors.white;
  static const Color textDark = Color(0xFF2D2D2D);
  static const Color textGrey = Color(0xFF8A8A8E);
  static const Color borderPink = Color(0xFFFFD2DC);
  static const Color successGreen = Color(0xFF10B981);

  static const Color badgeYellowBg = Color(0xFFFFF8E6);
  static const Color badgeYellowText = Color(0xFFF5A623);
  static const Color badgeBlueBg = Color(0xFFEFF6FF);
  static const Color badgeBlueText = Color(0xFF2563EB);
  static const Color badgeGreenBg = Color(0xFFECFDF5);
  static const Color badgeGreenText = Color(0xFF059669);
}

class UserSession {
  static Map<String, dynamic>? currentUser;
  static String? _token;
  static const _sessionKey = 'sapa_user_session';

  static String get name =>
      currentUser?['name'] ?? currentUser?['nama'] ?? 'Pengguna';
  static String get email => currentUser?['email'] ?? 'pengguna@gmail.com';
  static int? get id => currentUser?['id'];

  static Future<void> restore() async {
    final preferences = await SharedPreferences.getInstance();
    final storedSession = preferences.getString(_sessionKey);
    if (storedSession == null) return;

    final decoded = jsonDecode(storedSession);
    if (decoded is! Map<String, dynamic> ||
        decoded['token'] is! String ||
        decoded['token'].isEmpty) {
      throw const FormatException('Data sesi tersimpan tidak valid.');
    }

    currentUser = Map<String, dynamic>.from(decoded);
    _token = decoded['token'] as String;
  }

  static Future<void> save(Map<String, dynamic> user) async {
    final token = user['token'];
    if (token is! String || token.isEmpty) {
      throw const FormatException('Token autentikasi tidak ditemukan.');
    }

    currentUser = Map<String, dynamic>.from(user);
    _token = token;
    final preferences = await SharedPreferences.getInstance();
    await preferences.setString(_sessionKey, jsonEncode(currentUser));
  }

  static Future<void> clear() async {
    currentUser = null;
    _token = null;
    final preferences = await SharedPreferences.getInstance();
    await preferences.remove(_sessionKey);
  }

  static Future<void> logout() async {
    try {
      if (_token != null) {
        final response = await http.post(
          Uri.parse('${AppTheme.apiUrl}/logout'),
          headers: authHeaders(),
        );
        if (response.statusCode != 200 && response.statusCode != 401) {
          throw StateError('Server gagal mengakhiri sesi.');
        }
      }
    } finally {
      await clear();
    }
  }

  static Map<String, String> authHeaders({bool json = false}) => {
        'Accept': 'application/json',
        if (json) 'Content-Type': 'application/json',
        if (_token != null) 'Authorization': 'Bearer $_token',
      };
}
