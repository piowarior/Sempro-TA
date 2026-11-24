import 'package:flutter/material.dart';

class ExaminationDetailScreen extends StatelessWidget {
  final Map<String, dynamic> session;

  const ExaminationDetailScreen({super.key, required this.session});

  @override
  Widget build(BuildContext context) {
    final toxin = session['toxin_assessment'];
    final body = session['body_measurement'];
    final blood = session['blood_measurement'];
    final answers = toxin?['answers'] as List<dynamic>? ?? [];
    final indicatedAnswers = answers.where((a) => a['is_indicated'] == true).toList();

    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        title: Text(session['session_number'] ?? 'Detail Sesi', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
        backgroundColor: Colors.white,
        foregroundColor: const Color(0xFF0F172A),
        elevation: 0,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            // Session Date Card
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(16),
                border: Border.all(color: const Color(0xFFE2E8F0)),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text('Tanggal Pemeriksaan: ${session['session_date']}', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Color(0xFF0F172A))),
                  if (session['staff_notes'] != null && (session['staff_notes'] as String).isNotEmpty)
                    Padding(
                      padding: const EdgeInsets.only(top: 8.0),
                      child: Text('Catatan Petugas: ${session['staff_notes']}', style: const TextStyle(fontSize: 12, color: Color(0xFF475569))),
                    ),
                  if (session['examination_summary'] != null && (session['examination_summary'] as String).isNotEmpty)
                    Padding(
                      padding: const EdgeInsets.only(top: 8.0),
                      child: Text('Ringkasan Evaluasi: ${session['examination_summary']}', style: const TextStyle(fontSize: 12, color: Color(0xFF059669), fontWeight: FontWeight.w500)),
                    ),
                ],
              ),
            ),

            const SizedBox(height: 16),

            // Toxin Assessment Card
            if (toxin != null)
              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(color: const Color(0xFFE2E8F0)),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        const Text('Toxin Assessment', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Color(0xFF0F172A))),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                          decoration: BoxDecoration(
                            color: toxin['level'] == 'Level 1' ? const Color(0xFFD1FAE5) : (toxin['level'] == 'Level 2' ? const Color(0xFFFEF3C7) : const Color(0xFFFFE4E6)),
                            borderRadius: BorderRadius.circular(6),
                          ),
                          child: Text(
                            '${toxin['level']} (${toxin['total_indicated']}/50)',
                            style: TextStyle(
                              fontSize: 10,
                              fontWeight: FontWeight.bold,
                              color: toxin['level'] == 'Level 1' ? const Color(0xFF065F46) : (toxin['level'] == 'Level 2' ? const Color(0xFF92400E) : const Color(0xFF9F1239)),
                            ),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 8),
                    const Text('Indikator yang Terindikasi:', style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Color(0xFF64748B))),
                    const SizedBox(height: 6),
                    if (indicatedAnswers.isNotEmpty)
                      ...indicatedAnswers.map((a) => Padding(
                            padding: const EdgeInsets.only(bottom: 4),
                            child: Row(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                const Text('• ', style: TextStyle(color: Color(0xFF059669), fontWeight: FontWeight.bold)),
                                Expanded(
                                  child: Text(
                                    '#${a['indicator_number']} ${a['indicator_name']}',
                                    style: const TextStyle(fontSize: 11, color: Color(0xFF334155)),
                                  ),
                                ),
                              ],
                            ),
                          ))
                    else
                      const Text('Tidak ada keluhan toksin terindikasi.', style: TextStyle(fontSize: 11, color: Color(0xFF94A3B8))),
                  ],
                ),
              ),

            const SizedBox(height: 16),

            // Body Parameters Card
            if (body != null)
              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(color: const Color(0xFFE2E8F0)),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text('Parameter Komposisi Tubuh', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Color(0xFF0F172A))),
                    const SizedBox(height: 12),
                    Wrap(
                      spacing: 12,
                      runSpacing: 10,
                      children: [
                        _ParamItem(label: 'Berat Badan', value: body['weight'] != null ? '${body['weight']} kg' : null),
                        _ParamItem(label: 'BMI', value: body['bmi'] != null ? '${body['bmi']}' : null),
                        _ParamItem(label: 'Lingkar Pinggang', value: body['waist_circumference'] != null ? '${body['waist_circumference']} cm' : null),
                        _ParamItem(label: 'Body Fat', value: body['body_fat'] != null ? '${body['body_fat']} %' : null),
                        _ParamItem(label: 'Total Water', value: body['water'] != null ? '${body['water']} %' : null),
                        _ParamItem(label: 'Massa Otot', value: body['muscle_mass'] != null ? '${body['muscle_mass']} kg' : null),
                        _ParamItem(label: 'Visceral Fat', value: body['visceral_fat'] != null ? '${body['visceral_fat']}' : null),
                        _ParamItem(label: 'Suhu Tubuh', value: body['body_temperature'] != null ? '${body['body_temperature']} °C' : null),
                      ],
                    ),
                  ],
                ),
              ),

            const SizedBox(height: 16),

            // Blood Parameters Card
            if (blood != null)
              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(color: const Color(0xFFE2E8F0)),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text('Parameter Darah & Ketosis', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Color(0xFF0F172A))),
                    const SizedBox(height: 12),
                    Wrap(
                      spacing: 12,
                      runSpacing: 10,
                      children: [
                        _ParamItem(label: 'Tekanan Darah', value: blood['blood_pressure']),
                        _ParamItem(label: 'Heart Rate', value: blood['heart_rate'] != null ? '${blood['heart_rate']} bpm' : null),
                        _ParamItem(label: 'Glukosa', value: blood['glucose'] != null ? '${blood['glucose']} mg/dL' : null),
                        _ParamItem(label: 'Ketone Darah', value: blood['ketone'] != null ? '${blood['ketone']} mmol/L' : null),
                        _ParamItem(label: 'Kolesterol', value: blood['cholesterol'] != null ? '${blood['cholesterol']} mg/dL' : null),
                        _ParamItem(label: 'Asam Urat', value: blood['uric_acid'] != null ? '${blood['uric_acid']} mg/dL' : null),
                        _ParamItem(label: 'Hemoglobin', value: blood['hemoglobin'] != null ? '${blood['hemoglobin']} g/dL' : null),
                      ],
                    ),
                  ],
                ),
              ),
          ],
        ),
      ),
    );
  }
}

class _ParamItem extends StatelessWidget {
  final String label;
  final String? value;

  const _ParamItem({required this.label, this.value});

  @override
  Widget build(BuildContext context) {
    if (value == null) return const SizedBox.shrink();
    return Container(
      width: 140,
      padding: const EdgeInsets.all(10),
      decoration: BoxDecoration(
        color: const Color(0xFFF8FAFC),
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: const Color(0xFFF1F5F9)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(label, style: const TextStyle(fontSize: 10, color: Color(0xFF64748B))),
          const SizedBox(height: 2),
          Text(value!, style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Color(0xFF0F172A))),
        ],
      ),
    );
  }
}
