import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import '../config/api_config.dart';

class ApiService {
  static const String tokenKey = 'auth_token';
  static const String userKey = 'auth_user';

  static Future<String?> getToken() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString(tokenKey);
  }

  static Future<Map<String, dynamic>?> getSavedUser() async {
    final prefs = await SharedPreferences.getInstance();
    final userStr = prefs.getString(userKey);
    if (userStr != null) {
      return jsonDecode(userStr);
    }
    return null;
  }

  static Future<Map<String, dynamic>> login(String username, String password) async {
    final response = await http.post(
      Uri.parse('${ApiConfig.baseUrl}/auth/login'),
      headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
      body: jsonEncode({'username': username, 'password': password}),
    );

    final data = jsonDecode(response.body);
    if (response.statusCode >= 200 && response.statusCode < 300) {
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString(tokenKey, data['token']);
      await prefs.setString(userKey, jsonEncode(data['user']));
      return {'success': true, 'data': data};
    }

    final message = data['message'] ?? (data['errors'] != null ? data['errors'].values.first[0] : 'Login gagal');
    return {'success': false, 'message': message};
  }

  static Future<void> logout() async {
    final token = await getToken();
    if (token != null) {
      try {
        await http.post(
          Uri.parse('${ApiConfig.baseUrl}/auth/logout'),
          headers: {'Authorization': 'Bearer $token', 'Accept': 'application/json'},
        );
      } catch (_) {}
    }
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(tokenKey);
    await prefs.remove(userKey);
  }

  static Future<Map<String, dynamic>> getDashboard() async {
    final token = await getToken();
    final response = await http.get(
      Uri.parse('${ApiConfig.baseUrl}/patient/dashboard'),
      headers: {'Authorization': 'Bearer $token', 'Accept': 'application/json'},
    );

    if (response.statusCode == 200) {
      return jsonDecode(response.body);
    }
    throw Exception('Gagal memuat dashboard: ${response.statusCode}');
  }

  static Future<List<dynamic>> getExaminations() async {
    final token = await getToken();
    final response = await http.get(
      Uri.parse('${ApiConfig.baseUrl}/patient/examinations'),
      headers: {'Authorization': 'Bearer $token', 'Accept': 'application/json'},
    );

    if (response.statusCode == 200) {
      return jsonDecode(response.body);
    }
    throw Exception('Gagal memuat riwayat pemeriksaan');
  }

  static Future<List<dynamic>> getActivities() async {
    final token = await getToken();
    final response = await http.get(
      Uri.parse('${ApiConfig.baseUrl}/patient/activities'),
      headers: {'Authorization': 'Bearer $token', 'Accept': 'application/json'},
    );

    if (response.statusCode == 200) {
      return jsonDecode(response.body);
    }
    throw Exception('Gagal memuat daftar aktivitas');
  }

  static Future<Map<String, dynamic>> submitReport({
    required int planId,
    required String activityDate,
    required String executionStatus,
    String? patientNotes,
    int? scheduleId,
  }) async {
    final token = await getToken();
    final body = {
      'activity_date': activityDate,
      'execution_status': executionStatus,
      'patient_notes': patientNotes ?? '',
      if (scheduleId != null) 'activity_schedule_id': scheduleId,
    };

    final response = await http.post(
      Uri.parse('${ApiConfig.baseUrl}/patient/activities/$planId/report'),
      headers: {
        'Authorization': 'Bearer $token',
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: jsonEncode(body),
    );

    final data = jsonDecode(response.body);
    if (response.statusCode >= 200 && response.statusCode < 300) {
      return {'success': true, 'data': data};
    }
    return {'success': false, 'message': data['message'] ?? 'Gagal mengirim laporan'};
  }
}
