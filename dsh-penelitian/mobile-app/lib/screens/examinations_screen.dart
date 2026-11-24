import 'package:flutter/material.dart';
import '../services/api_service.dart';
import 'examination_detail_screen.dart';

class ExaminationsScreen extends StatefulWidget {
  const ExaminationsScreen({super.key});

  @override
  State<ExaminationsScreen> createState() => _ExaminationsScreenState();
}

class _ExaminationsScreenState extends State<ExaminationsScreen> {
  bool _isLoading = true;
  List<dynamic> _sessions = [];
  String? _errorMessage;

  @override
  void initState() {
    super.initState();
    _fetchExaminations();
  }

  Future<void> _fetchExaminations() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      final data = await ApiService.getExaminations();
      if (mounted) {
        setState(() {
          _sessions = data;
          _isLoading = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _errorMessage = e.toString();
          _isLoading = false;
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        title: const Text('Riwayat Pemeriksaan', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18)),
        backgroundColor: Colors.white,
        foregroundColor: const Color(0xFF0F172A),
        elevation: 0,
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator(color: Color(0xFF059669)))
          : _errorMessage != null
              ? Center(child: Text(_errorMessage!, style: const TextStyle(fontSize: 12)))
              : RefreshIndicator(
                  onRefresh: _fetchExaminations,
                  color: const Color(0xFF059669),
                  child: _sessions.isEmpty
                      ? const Center(
                          child: Text('Belum ada data pemeriksaan.', style: TextStyle(fontSize: 12, color: Color(0xFF94A3B8))),
                        )
                      : ListView.builder(
                          padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
                          itemCount: _sessions.length,
                          itemBuilder: (context, index) {
                            final session = _sessions[index];
                            final toxin = session['toxin_assessment'];
                            final body = session['body_measurement'];
                            final blood = session['blood_measurement'];

                            return Card(
                              margin: const EdgeInsets.only(bottom: 14),
                              elevation: 0,
                              shape: RoundedRectangleBorder(
                                borderRadius: BorderRadius.circular(16),
                                side: const BorderSide(color: Color(0xFFE2E8F0)),
                              ),
                              child: InkWell(
                                borderRadius: BorderRadius.circular(16),
                                onTap: () {
                                  Navigator.of(context).push(
                                    MaterialPageRoute(
                                      builder: (_) => ExaminationDetailScreen(session: session),
                                    ),
                                  );
                                },
                                child: Padding(
                                  padding: const EdgeInsets.all(16),
                                  child: Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      Row(
                                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                        children: [
                                          Text(
                                            session['session_number'] ?? 'Sesi Pemeriksaan',
                                            style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: Color(0xFF0F172A)),
                                          ),
                                          if (toxin != null)
                                            Container(
                                              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                                              decoration: BoxDecoration(
                                                color: toxin['level'] == 'Level 1'
                                                    ? const Color(0xFFD1FAE5)
                                                    : (toxin['level'] == 'Level 2' ? const Color(0xFFFEF3C7) : const Color(0xFFFFE4E6)),
                                                borderRadius: BorderRadius.circular(6),
                                              ),
                                              child: Text(
                                                toxin['level'] ?? 'Level 1',
                                                style: TextStyle(
                                                  fontSize: 10,
                                                  fontWeight: FontWeight.bold,
                                                  color: toxin['level'] == 'Level 1'
                                                      ? const Color(0xFF065F46)
                                                      : (toxin['level'] == 'Level 2' ? const Color(0xFF92400E) : const Color(0xFF9F1239)),
                                                ),
                                              ),
                                            ),
                                        ],
                                      ),
                                      const SizedBox(height: 4),
                                      Text(
                                        'Tanggal: ${session['session_date']}',
                                        style: const TextStyle(fontSize: 11, color: Color(0xFF64748B)),
                                      ),
                                      const SizedBox(height: 12),
                                      Row(
                                        children: [
                                          if (body != null && body['weight'] != null)
                                            _MiniTag(label: 'BB: ${body['weight']} kg'),
                                          if (blood != null && blood['glucose'] != null)
                                            _MiniTag(label: 'Gula: ${blood['glucose']} mg/dL'),
                                          if (blood != null && blood['blood_pressure'] != null)
                                            _MiniTag(label: 'TD: ${blood['blood_pressure']}'),
                                        ],
                                      ),
                                      if (session['examination_summary'] != null && (session['examination_summary'] as String).isNotEmpty)
                                        Padding(
                                          padding: const EdgeInsets.only(top: 10.0),
                                          child: Text(
                                            session['examination_summary'],
                                            style: const TextStyle(fontSize: 11, color: Color(0xFF475569)),
                                            maxLines: 2,
                                            overflow: TextOverflow.ellipsis,
                                          ),
                                        ),
                                    ],
                                  ),
                                ),
                              ),
                            );
                          },
                        ),
                ),
    );
  }
}

class _MiniTag extends StatelessWidget {
  final String label;
  const _MiniTag({required this.label});

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.only(right: 6),
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
      decoration: BoxDecoration(
        color: const Color(0xFFF1F5F9),
        borderRadius: BorderRadius.circular(6),
      ),
      child: Text(label, style: const TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: Color(0xFF334155))),
    );
  }
}
