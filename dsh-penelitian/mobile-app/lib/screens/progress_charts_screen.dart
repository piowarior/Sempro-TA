import 'package:flutter/material.dart';
import 'package:fl_chart/fl_chart.dart';
import '../services/api_service.dart';

class ProgressChartsScreen extends StatefulWidget {
  const ProgressChartsScreen({super.key});

  @override
  State<ProgressChartsScreen> createState() => _ProgressChartsScreenState();
}

class _ProgressChartsScreenState extends State<ProgressChartsScreen> {
  bool _isLoading = true;
  List<dynamic> _sessions = [];
  String? _errorMessage;

  @override
  void initState() {
    super.initState();
    _fetchData();
  }

  Future<void> _fetchData() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      final data = await ApiService.getExaminations();
      // Reverse to chronological order for charts (oldest to newest)
      final reversed = List.from(data.reversed);
      if (mounted) {
        setState(() {
          _sessions = reversed;
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
        title: const Text('Grafik Perkembangan', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18)),
        backgroundColor: Colors.white,
        foregroundColor: const Color(0xFF0F172A),
        elevation: 0,
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator(color: Color(0xFF059669)))
          : _errorMessage != null
              ? Center(child: Text(_errorMessage!, style: const TextStyle(fontSize: 12)))
              : _sessions.length < 2
                  ? const Center(
                      child: Padding(
                        padding: EdgeInsets.all(24.0),
                        child: Text(
                          'Dibutuhkan setidaknya 2 sesi pemeriksaan untuk melihat tren perkembangan grafik.',
                          textAlign: TextAlign.center,
                          style: TextStyle(fontSize: 12, color: Color(0xFF64748B)),
                        ),
                      ),
                    )
                  : SingleChildScrollView(
                      padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.stretch,
                        children: [
                          // 1. Toxin Assessment Trend
                          _buildChartCard(
                            title: 'Tren Toxin Assessment (0 - 50)',
                            color: const Color(0xFF059669),
                            spots: _extractSpots((s) => s['toxin_assessment']?['total_indicated']),
                          ),
                          const SizedBox(height: 16),

                          // 2. Berat Badan (kg)
                          _buildChartCard(
                            title: 'Berat Badan (kg)',
                            color: const Color(0xFF2563EB),
                            spots: _extractSpots((s) => s['body_measurement']?['weight']),
                          ),
                          const SizedBox(height: 16),

                          // 3. Glukosa Puasa (mg/dL)
                          _buildChartCard(
                            title: 'Glukosa Puasa (mg/dL)',
                            color: const Color(0xFFE11D48),
                            spots: _extractSpots((s) => s['blood_measurement']?['glucose']),
                          ),
                          const SizedBox(height: 16),

                          // 4. Ketone Darah (mmol/L)
                          _buildChartCard(
                            title: 'Ketone Darah (mmol/L)',
                            color: const Color(0xFFD97706),
                            spots: _extractSpots((s) => s['blood_measurement']?['ketone']),
                          ),
                        ],
                      ),
                    ),
    );
  }

  List<FlSpot> _extractSpots(num? Function(Map<String, dynamic>) extractor) {
    final List<FlSpot> spots = [];
    for (int i = 0; i < _sessions.length; i++) {
      final val = extractor(_sessions[i]);
      if (val != null) {
        spots.add(FlSpot(i.toDouble(), val.toDouble()));
      }
    }
    return spots;
  }

  Widget _buildChartCard({required String title, required Color color, required List<FlSpot> spots}) {
    if (spots.isEmpty) return const SizedBox.shrink();

    return Container(
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
            children: [
              Container(width: 8, height: 8, decoration: BoxDecoration(color: color, shape: BoxShape.circle)),
              const SizedBox(width: 8),
              Text(title, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Color(0xFF0F172A))),
            ],
          ),
          const SizedBox(height: 16),
          SizedBox(
            height: 180,
            child: LineChart(
              LineChartData(
                gridData: FlGridData(show: true, drawVerticalLine: false, horizontalInterval: 1),
                titlesData: FlTitlesData(
                  rightTitles: const AxisTitles(sideTitles: SideTitles(showTitles: false)),
                  topTitles: const AxisTitles(sideTitles: SideTitles(showTitles: false)),
                  bottomTitles: AxisTitles(
                    sideTitles: SideTitles(
                      showTitles: true,
                      reservedSize: 22,
                      getTitlesWidget: (val, meta) {
                        final idx = val.toInt();
                        if (idx >= 0 && idx < _sessions.length) {
                          final date = _sessions[idx]['session_date'] as String? ?? '';
                          return Text(date.length > 5 ? date.substring(5) : date, style: const TextStyle(fontSize: 9, color: Color(0xFF64748B)));
                        }
                        return const Text('');
                      },
                    ),
                  ),
                ),
                borderData: FlBorderData(show: false),
                lineBarsData: [
                  LineChartBarData(
                    spots: spots,
                    isCurved: true,
                    color: color,
                    barWidth: 3,
                    isStrokeCapRound: true,
                    dotData: const FlDotData(show: true),
                    belowBarData: BarAreaData(
                      show: true,
                      color: color.withOpacity(0.08),
                    ),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}
